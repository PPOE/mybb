<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'fetchnews.php');
define("IN_SYNCOM", 1);

$basepath = dirname($_SERVER["SCRIPT_FILENAME"]);

require_once $basepath."/../global.php";

require MYBB_ROOT.'/syncom/config.php';

require_once 'Net/NNTP/Client.php';
require_once 'Mail/RFC822.php';
require_once 'Mail.php';
require_once 'Mail/mime.php';

require_once "convertpost.php";

require_once "mybbapi.php";

function check_utf8($str) {
    $len = strlen($str);
    for($i = 0; $i < $len; $i++){
        $c = ord($str[$i]);
        if ($c > 128) {
            if (($c > 247)) return false;
            elseif ($c > 239) $bytes = 4;
            elseif ($c > 223) $bytes = 3;
            elseif ($c > 191) $bytes = 2;
            else return false;
            if (($i + $bytes) > $len) return false;
            while ($bytes > 1) {
                $i++;
                $b = ord($str[$i]);
                if ($b < 128 || $b > 191) return false;
                $bytes--;
            }
        }
    }
    return true;
}

function fetcharticles($nntp, $newsgroup, $start, $end = -1)
{
	global $syncom;
	$ret = $nntp->selectGroup($newsgroup);
	if(PEAR::isError($ret)) {
		echo $ret->message."\r\n".$ret->userinfo."\r\n";
		return(false);
	}

	$first = $nntp->first();

	if ($start > $first)
		$first = $start;

	$last = $nntp->last();

	if (($end < $last) and ($end != -1))
		$last = $end;

	for ($i = $first; $i <= $last; $i++) {
		$article = $nntp->getArticle($i, true);
		if(!PEAR::isError($article)) {
			file_put_contents($syncom['incoming-spool'].'/'.$newsgroup.'-'.substr('00000000'.$i, -8), 
					serialize(array('newsgroup' => $newsgroup, 'number' => $i, 'article' => $article)));
		}
                else {
	                echo $ret->message."\r\n".$ret->userinfo."\r\n";
	        }

	}

	return($last);
}

function processarticle($api, $fid, $article, $articlenumber)
{
	global $db, $syncom;
$pid = pcntl_fork();
if ($pid == -1) {
     die('Konnte nicht verzweigen');
} else if ($pid) {
     pcntl_wait($status);
     return $status;
} else {
	//echo "Sonderbehandlung fuer einen einzelnen Fall\n";
	//echo "schneller Bugfix - die Ursache des Fehlers muss\n";
	//echo "noch ergründet werden\n";
//	if (($articlenumber == 21730) and ($fid == 49))
//		return(false);

	//echo "Zerlegen der Nachricht\n";
	$struct = convertpost($article);

	if ($struct['from']['host'] == null || strlen($struct['from']['host'] < 2))
		$struct['from']['host'] = 'forum.piratenpartei.at';

	//echo "x-no-archive wird nicht uebertragen\n";
	if (strtolower($struct['x-no-archive']) == 'yes') {
		echo "X-No-Archive\r\n";
		$struct['body'] = '(X-No-Archive)';
		$struct['from']['mailbox'] = 'nobody';
		$struct['from']['host'] = 'nowhere.tld';
		$struct['from']['personal'] = 'nobody';
	}

	if (strtolower(substr($struct['body'],0,17)) == 'x-no-archive: yes') {
		echo "X-No-Archive\r\n";
		$struct['body'] = '(X-No-Archive)';
		$struct['from']['mailbox'] = 'nobody';
		$struct['from']['host'] = 'nowhere.tld';
		$struct['from']['personal'] = 'nobody';
	}

	//echo "Erkennen eines Supersedes\n";
	$supersede = (strtolower($struct['supersedes']) != '');

	if ($supersede) {
		$post = $api->getidbymessageid($struct['supersedes'], $fid);
		if (intval($post['pid']) == 0)
			$supersede = false;
	}

	//echo "wurde die Nachricht bereits gepostet?\n";
	$post = $api->getidbymessageid($struct['message-id'], $fid);

	$isnewmessage = ($post['syncom_articlenumber'] != $articlenumber);

	//echo "Pruefung, ob der Artikel bereits ohne Nummer existiert\n";
	if (($post['syncom_articlenumber'] != $articlenumber) and (intval($post['pid']) != 0)) {
		echo "Insert articlenumber\r\n";
		$db->update_query("posts", array('syncom_articlenumber'=>$articlenumber, 'visible'=>1), "pid=".intval($post['pid']));

		if (!$post['visible']) {
			echo "Publish thread, update counter\r\n";
			$query = $db->simple_select("threads", "replies, unapprovedposts, visible", "tid=".intval($post['tid']), array('limit' => 1));
			$thread = $db->fetch_array($query);
			$replies = $thread['replies'];
			$unapprovedposts = $thread['unapprovedposts'];
			if ($unapprovedposts > 0) {
				$replies++;
				$unapprovedposts--;
			}
			$db->update_query("threads", array('visible'=>1, 'replies'=>($replies)+0, 'unapprovedposts'=>($unapprovedposts)+0), "tid=".intval($post['tid']));

			$query = $db->simple_select("forums", "unapprovedthreads,unapprovedposts,threads,posts", "fid=".intval($post['fid']), array('limit' => 1));
			$forum = $db->fetch_array($query);
			$threads = $forum['threads'];
			$posts = $forum['posts'];
			$unapprovedthreads = $forum['unapprovedthreads'];
			$unapprovedposts = $forum['unapprovedposts'];
			if ($unapprovedposts > 0) {
				$posts++;
				$unapprovedposts--;
			}
			if (!$thread['visible']) {
				$threads++;
				$unapprovedthreads--;
			}
			$db->update_query("forums", array('threads'=>intval($threads), 'posts'=>intval($posts), 'unapprovedposts'=>intval($unapprovedposts), 'unapprovedthreads'=>intval($unapprovedthreads)), "fid=".intval($post['fid']));
		}
	}

	//echo "wenn ja und kein Supersede => nicht posten\n";
	if ((intval($post['pid']) != 0) and !$supersede) {

		//echo "Mail-Out - wenn der Artikel aus dem Forum erzeugt wurde\n";
	//	echo "Aber nur, wenn der Artikel nicht bereits ins Forum zurueckkam\n";
/*		if ($isnewmessage) {
			$temp = tempnam($syncom['mailout-spool']."/", "mout1");
			file_put_contents($temp, serialize(array("info"=>$post, "message"=>$article)));
		}*/

		echo "already posted\r\n";
		return(true);
	}

	if (!$supersede) {
		$post = array('tid'=>0, 'pid'=>0, 'uid'=>0);;

		//echo "Anhand der References den letzten Artikel finden\n";
		foreach ($struct['references'] as $references) {
			$postref = $api->getidbymessageid($references, $fid);

			if ($postref['tid'] != 0)
				$post = $postref;
		}
	}

	//echo "Und dann schauen, ob es den gleichen Betreff innerhalb von X Tagen gab\n";

        if (!mb_detect_encoding($struct['subject'], 'UTF-8', true))
        {
                $struct['subject'] = mb_convert_encoding($struct['subject'], "ISO-8859-1", "UTF-8");
        }

	$struct['subject'] = substr($struct['subject'], 0, 84);
        $struct['subject'] = $db->escape_string($struct['subject']);

	if (!mb_detect_encoding($struct['body'], 'UTF-8', true))
	{
		$struct['body'] = mb_convert_encoding($struct['body'], "ISO-8859-1", "UTF-8");
	}

        if ($post['pid'] == 0)
		$post = $api->getidbysubject($struct, $fid);

	//echo "Wenn immer noch kein Bezug gefunden wird, wird das \"re:\" entfernt\n";
	if ($post['pid'] == 0)
		if (strtolower(substr($struct['subject'],0,3)) == 're:' || strtolower(substr($struct['subject'],0,3)) == 'aw:')
			$struct['subject'] = ltrim(substr($struct['subject'], 3));

	$user = $db->escape_string($struct['from']['personal']);

	if ($user == '')
		$user = $db->escape_string($struct['from']['mailbox']);

	$email = $db->escape_string($struct['from']['mailbox']).'@'.$db->escape_string($struct['from']['host']);

	$sender = $db->escape_string($struct['sender']['mailbox']).'@'.$db->escape_string($struct['sender']['host']);

	if ($sender == $syncom['syncuser'])
		$sender = '';

	$userdata = $api->getuserbymail($email, $sender);

	if ($supersede) {
		$old = $api->getidbymessageid($struct['supersedes'], $fid);

		$success = $api->edit(intval($old['tid']), intval($old['pid']), $old['replyto'], $struct['subject'], $db->escape_string($struct['body']),
			$userdata['uid'], $user, $struct['date'], $struct['message-id'], $articlenumber, $email);
		return($success);
	} else {
		$success = $api->post(intval($fid), intval($post['tid']), intval($post['pid']), $struct['subject'], $db->escape_string($struct['body']),
			$userdata['uid'], $user, $struct['date'], $struct['message-id'], $articlenumber, $email);
		if ($success) {
			$postedmsg = $api->getidbymessageid($struct['message-id'], $fid);
	 		$db->update_query("posts", array('syncom_articlenumber'=>$articlenumber, 'visible'=>1), "pid=".intval($postedmsg['pid']));

			//if (!$postedmsg['visible']) {
				echo "Publish thread, update counter\r\n";
				$query = $db->simple_select("threads", "replies, unapprovedposts, visible", "tid=".intval($postedmsg['tid']), array('limit' => 1));
				$thread = $db->fetch_array($query);
				$replies = $thread['replies'];
				$unapprovedposts = $thread['unapprovedposts'];
				if ($unapprovedposts > 0) {
					$replies++;
					$unapprovedposts--;
				}
				$db->update_query("threads", array('visible'=>1, 'replies'=>($replies + 0), 'unapprovedposts'=>($unapprovedposts + 0)), "tid=".intval($postedmsg['tid']));

				$query = $db->simple_select("forums", "unapprovedthreads,unapprovedposts,threads,posts", "fid=".intval($postedmsg['fid']), array('limit' => 1));
				$forum = $db->fetch_array($query);
				$threads = $forum['threads'];
				$posts = $forum['posts'];
				$unapprovedthreads = $forum['unapprovedthreads'];
				$unapprovedposts = $forum['unapprovedposts'];
				if ($unapprovedposts > 0) {
					$posts++;
					$unapprovedposts--;
				}
				if (!$thread['visible']) {
					$threads++;
					$unapprovedthreads--;
				}
				$db->update_query("forums", array('threads'=>intval($threads), 'posts'=>intval($posts), 'unapprovedposts'=>intval($unapprovedposts), 'unapprovedthreads'=>intval($unapprovedthreads)), "fid=".intval($postedmsg['fid']));
			//}

			$post = $api->getidbymessageid($struct['message-id'], $fid);
	//		echo "Mail-Out - wenn der Artikel aus der Newsgroup kam\n";
/*			if ($post['pid'] != 0) {
				$temp = tempnam($syncom['mailout-spool']."/", "mout2");
				file_put_contents($temp, serialize(array("info"=>$post, "message"=>$article)));
			}*/
		} else
			if ($struct['body'] == '')
				return(true);

		return($success);
	}
    }
}

function processarticles()
{
	global $syncom;
       // echo "processarticles()\n";
	$api = new mybbapi;

	$dir = scandir($syncom['incoming-spool'].'/');

	foreach ($dir as $spoolfile) {
		$file = $syncom['incoming-spool'].'/'.$spoolfile;
               // echo "$file\n";
		if (!is_dir($file) and (file_exists($file))) {
                       // echo "is a file\n";
			$message = unserialize(file_get_contents($file));
                        // echo "$message\n";
			$fid = $api->getforumid($message['newsgroup']);

			echo $fid." - ".$file."\r\n";

			if (($fid == 0) or processarticle($api, $fid, $message['article'], $message['number']))
				@unlink($file);
			else
				@rename($file, $syncom['incoming-spool'].'/error/'.$spoolfile);
		}
	}
}

function fetchgroups()
{
	global $db, $syncom;

	$query = $db->simple_select("forums", "fid, syncom_newsgroup", "syncom_newsgroup!=''", array("order_by" => "syncom_newsgroup"));

	$newsgroups = array();
	while ($forum = $db->fetch_array($query))
		$newsgroups[$forum['fid']] = $forum['syncom_newsgroup'];

	$nntp = new Net_NNTP_Client();
        $ret = $nntp->connect();
	//$ret = $nntp->connect($syncom['newsserver'], null, 119, 3);
	if(PEAR::isError($ret)) {
		echo $ret->message."\r\n".$ret->userinfo."\r\n";
		return(false);
	}
        $nntp->cmdModeReader();
        if(PEAR::isError($ret)) {
                echo $ret->message."\r\n".$ret->userinfo."\r\n";
                return(false);
        }
/*	if ($syncom['user'] != '') {
                echo $syncom['user'] . ", " . $syncom['password'] . "\n";
		$ret = $nntp->authenticate();//$syncom['user'], $syncom['password']);
		if(PEAR::isError($ret)) {
			echo $ret->message."\r\n".$ret->userinfo."\r\n";
			return(false);
		}
	}*/
  $query = $db->simple_select("posts", "DISTINCT ON (fid) syncom_articlenumber,fid", "fid IN (".implode(",",array_keys($newsgroups)).")", array("order_by" => "fid asc, syncom_articlenumber desc"));
  while ($post = $db->fetch_array($query))
  {
    $forums[$post['fid']] = $post;
  }
  foreach ($newsgroups as $fid => $newsgroup) {
    if (intval($forums[$fid]['syncom_articlenumber']) > 0)
			$lastpost = $forums[$fid]["syncom_articlenumber"];
		else
			$lastpost = -1;

		if (file_exists($syncom['newsrc']))
			$newsrc = unserialize(file_get_contents($syncom['newsrc']));

		if ($newsrc[$newsgroup] < $lastpost)
			$newsrc[$newsgroup] = $lastpost;

		$ret = fetcharticles($nntp, $newsgroup, $newsrc[$newsgroup] + 1);
		if(PEAR::isError($ret)) {
			echo $ret->message."\r\n".$ret->userinfo."\r\n";
		} else {
			$newsrc[$newsgroup] = $ret;
			file_put_contents($syncom['newsrc'], serialize($newsrc));
		}
		processarticles();
	}
}
// Newsgroups -> Eingangsspool
fetchgroups();
// Eingangsspool -> Forum
processarticles();

?>
