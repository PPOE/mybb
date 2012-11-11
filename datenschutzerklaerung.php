<?php
/**
 * MyBB 1.6
 * Copyright 2010 MyBB Group, All Rights Reserved
 *
 * Website: http://mybb.com
 * License: http://mybb.com/about/license
 *
 * $Id: misc.php 5821 2012-05-02 15:40:38Z Tomm $
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'misc.php');

$templatelist = "redirect_markallread,redirect_markforumread";
$templatelist .= ",misc_buddypopup,misc_buddypopup_user_online,misc_buddypopup_user_offline,misc_buddypopup_user_sendpm";
$templatelist .= ",misc_smilies,misc_smilies_smilie,misc_help_section_bit,misc_help_section,misc_help";
require_once "./global.php";
require_once MYBB_ROOT."inc/functions_post.php";

// Load global language phrases
$lang->load("misc");

$plugins->run_hooks("misc_start");

if($mybb->input['action'] == "dstswitch" && $mybb->request_method == "post" && $mybb->user['uid'] > 0)
{
	if($mybb->user['dstcorrection'] == 2)
	{
		if($mybb->user['dst'] == 1)
		{
			$update_array = array("dst" => 0);
		}
		else
		{
			$update_array = array("dst" => 1);
		}
	}
	$db->update_query("users", $update_array, "uid='{$mybb->user['uid']}'");
	if(!$mybb->input['ajax'])
	{
		redirect("index.php", $lang->dst_settings_updated);
	}
	else
	{
		echo "done";
		exit;
	}
}
	$lang->load("helpdocs");
	$lang->load("helpsections");
	$lang->load("customhelpdocs");
	$lang->load("customhelpsections");

					$helpdoc['name'] = "Datenschutzerkl&auml;rung";
					$helpdoc['description'] = "Datenschutzerkl&auml;rung";
					$helpdoc['document'] = '
<h2>Informationen</h2>
<ul>
<li><a href="http://piratenpartei.at/rechtliches/impressum/">Impressum</a></li>
<li><a href="https://wiki.piratenpartei.at/wiki/Syncom">Information über Syncom</a></li>
</ul>
<h2>Forum</h2>
<p>Bei jedem Zugriff eines Nutzers auf dieses Internet-Angebot und bei jedem Abruf einer Datei werden Daten über diesen Vorgang vorübergehend in einer Protokolldatei gespeichert und verarbeitet.</p>

<p>Vor der Speicherung wird jeder Datensatz durch Veränderung der IP-Adresse anonymisiert.</p>

<p>Wir behalten uns vor &uuml;ber jeden Zugriff/Abruf folgende Daten zu speichern:</p>

<ul>
<li>anonymisierte IP-Adresse</li>
<li>Datum und Uhrzeit,</li>
<li>aufgerufene Seite/Name der abgerufenen Datei,</li>
<li>übertragene Datenmenge,</li>
<li>Meldung, ob der Zugriff/Abruf erfolgreich war.</li>
</ul>

<p>Diese Daten werden (falls sie &uuml;berhaupt erhoben werden) lediglich für statistische Zwecke und zur Verbesserung des Angebots ausgewertet und anschli&szlig;end gelöscht. Eine andere Verwendung oder Weitergabe an Dritte erfolgt nicht.</p>

<p>Bei der Nutzung unseres Forums werden persistente und session cookies gesetzt, um die Nutzung des Forums komfortabler zu machen, zum Beispiel um die Anmeldung über eine Sitzung hinaus im Forum zu erhalten oder den gewählten Sprachsl beizubehalten. Wer keine Speicherung seiner Sitzungsdaten möchte, sollte die Checkbox&quot;Angemeldet bleibe&quot; nicht ankreuzen und seinen Browser so einrichten, dass sämtliche Cookies beim Herunterfahren des Rechners gelöscht werden.</p>

<p>Vom Newsserver bzw. den Mailinglisten importierte Beiträge werden über den Eintrag im Sender-Header, beziehungsweise über den Absender (From-Header) einem Forenaccount zugeordnet, falls dieser existiert. Ist dieser nicht vorhanden, wird der Beitrag von einem unregistrierten Account erstellt.</p>

<h2>Newsserver</h2>
<p>Nach der erfolgten Anmeldung an diesem Forum erhält man zusätzlich zum Zugriff über den Webbrowser auch Zugriff auf die Beiträge dieses Forums über <a href="http://de.wikipedia.org/wiki/NNTP">NNTP</a>
 auf den Newsserver forum.piratenpartei.at. Die Benutzerkennung und das Passwort entsprechen dabei dem des Forums.</p>

<p>Bei jedem Zugriff eines Nutzers auf den Newsserver und bei jedem Abruf eines Artikels werden Daten über diesen Vorgang vorübergehengespeichert und verarbeitet.</p>

<p>Vor der Speicherung wird jeder Datensatz durch Veränderung der IP-Adresse anonymisiert.</p>

<p>Wir behalten uns vor über jeden Zugriff/Abruf folgende Datn zu speichern:</p>

<ul>
<li>anonymisierte IP-Adresse</li>
<li>Datum und Uhrzeit,</li>
<li>aufgerufene Newsgroup,</li>
<li>übertragene Datenmenge,</li>
<li>Meldung, ob der Zugriff/Abruf erfolgreich war.</li>
</ul>

<p>Diese Daten werden (falls sie &uuml;berhaupt erhoben werden) lediglich für statistische Zwecke und zur Verbesserung des Angebots ausgewertet und anschli&szlig;end gelöscht. Eine andere Verwendung oder Weitergabe an Dritte erfolgt nicht.</p>

<h2>Mailinglisten</h2>

<p>Dieses Forum verarbeitet Beiträge aus Mailinglisten der
Piratenpartei
&Ouml;sterreichs und ihrer Landesverbände und angeschlossenen Organisationen und stellt diese als Forenbeiträge dar. In umgekehrter Richtung werden in diesem Forum oder per Newsserver erstellte Beiträge auf die entsprechenden Mailinglisten übertragen. Um welche Mailinglisten es sich dabei handelt, ist im entsprechenden Forenbereich erkennbar.</p>

<p>Dabei werden folgende Daten zu den Mailinglisten übertragen:</p>
<ul>
<li>Benutzername (&uuml;bertragen im &quot;Sender&quot;-Header)</li>
<li>Mailadresse (Wird im Benutzerprofil unter "Mailadresse für den Sync:" keine zusätzliche Mailadresse eingetragen, wird eine Adresse im Format benutzername@servername übertragen)</li>
<li>Name (Wird im Benutzerprofil unter "Name für den Sync:" kein zusätzlicher Name eingetragen, wird der Anmeldename übertragen)</li>
</ul>
<p>Aufgrund der Funktionsweise von Mailinglisten ist es nicht möglich, einmal erstellte Beiträge von einer Mailingliste zu löschen oder einen Beitrag zu ände &Auml;ndert oder löscht man also im Forum einen Beitrag, so wird dieser zwar auf dem angeschlossenen Newsserver geändert bzw. gelöscht, eine Löschung wird aber nicht zur Mailingliste übertragen, e&Auml;nderung bewirkt ein erneutes Versenden der Nachricht. Dies gilt analog auch für auf dem Newsserver geänderte oder gelöschte Beiträge.</p>
';
					
			add_breadcrumb($helpdoc['name']);

			eval("\$helppage = \"".$templates->get("misc_help_helpdoc")."\";");
			output_page($helppage);

if($mybb->input['action'] == "clearcookies")
{
	$plugins->run_hooks("misc_clearcookies");
	
	if($mybb->input['key'] != $mybb->user['logoutkey'])
	{
		error($lang->error_invalidkey);
	}

	$remove_cookies = array('mybb', 'mybbuser', 'mybb[password]', 'mybb[lastvisit]', 'mybb[lastactive]', 'collapsed', 'mybb[forumread]', 'mybb[threadsread]', 'mybbadmin');

	if($mybb->settings['cookiedomain'])
	{
		foreach($remove_cookies as $name)
		{
			@my_setcookie($name, '', TIME_NOW-1, $mybb->settings['cookiepath'], $mybb->settings['cookiedomain']);
		}
	}
	else
	{
		foreach($remove_cookies as $name)
		{
			@my_setcookie($name, '', TIME_NOW-1, $mybb->settings['cookiepath']);
		}
	}
	redirect("index.php", $lang->redirect_cookiescleared);
}

function makesyndicateforums($pid="0", $selitem="", $addselect="1", $depth="", $permissions="")
{
	global $db, $forumcache, $permissioncache, $mybb, $selecteddone, $forumlist, $forumlistbits, $theme, $templates, $flist, $lang, $unviewable;
	static $unviewableforums;

	$pid = intval($pid);
	if(!$permissions)
	{
		$permissions = $mybb->usergroup;
	}

	if(!is_array($forumcache))
	{
		// Get Forums
		$query = $db->simple_select("forums", "*", "linkto = '' AND active!=0", array('order_by' => 'pid, disporder'));
		while($forum = $db->fetch_array($query))
		{
			$forumcache[$forum['pid']][$forum['disporder']][$forum['fid']] = $forum;
		}
	}

	if(!is_array($permissioncache))
	{
		$permissioncache = forum_permissions();
	}

	if(!$unviewableforums)
	{
		// Save our unviewable forums in an array
		$unviewableforums = explode(",", str_replace("'", "", $unviewable));
	}

	if(is_array($forumcache[$pid]))
	{
		foreach($forumcache[$pid] as $key => $main)
		{
			foreach($main as $key => $forum)
			{
				$perms = $permissioncache[$forum['fid']];
				if($perms['canview'] == 1 || $mybb->settings['hideprivateforums'] == 0)
				{
					if($flist[$forum['fid']])
					{
						$optionselected = "selected=\"selected\"";
						$selecteddone = "1";
					}
					else
					{
						$optionselected = '';
					}

					if($forum['password'] == '' && !in_array($forum['fid'], $unviewableforums) || $forum['password'] && $mybb->cookies['forumpass'][$forum['fid']] == md5($mybb->user['uid'].$forum['password']))
					{
						$forumlistbits .= "<option value=\"{$forum['fid']}\" $optionselected>$depth {$forum['name']}</option>\n";
					}

					if($forumcache[$forum['fid']])
					{
						$newdepth = $depth."&nbsp;&nbsp;&nbsp;&nbsp;";
						$forumlistbits .= makesyndicateforums($forum['fid'], $selitem, 0, $newdepth, $perms);
					}
				}
			}
		}
	}
	if($addselect)
	{
		if(!$selecteddone)
		{
			$addsel = " selected=\"selected\"";
		}
		$forumlist = "<select name=\"forums[]\" size=\"10\" multiple=\"multiple\">\n<option value=\"all\" $addsel>$lang->syndicate_all_forums</option>\n<option value=\"all\">----------------------</option>\n$forumlistbits\n</select>";
	}
	return $forumlist;
}

?>
