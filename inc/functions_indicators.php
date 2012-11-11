<?php
/**
 * MyBB 1.6
 * Copyright 2010 MyBB Group, All Rights Reserved
 *
 * Website: http://mybb.com
 * License: http://mybb.com/about/license
 *
 * $Id: functions_indicators.php 5831 2012-05-23 11:21:28Z Tomm $
 */

/**
 * Mark a particular thread as read for the current user.
 *
 * @param int The thread ID
 * @param int The forum ID of the thread
 */
function mark_thread_read($tid, $fid, $mark_time = 0)
{
    global $mybb, $db;

    // Can only do "true" tracking for registered users
    if ($mybb->settings['threadreadcut'] > 0 && $mybb->user['uid'])
    {
        // For registered users, store the information in the database.
        $query = $db->query("SELECT tid FROM ".TABLE_PREFIX."threadsread WHERE uid='{$mybb->user['uid']}' AND tid=$tid");
        if ($db->fetch_field($query, "tid"))
        {
                 $db->query("UPDATE ".TABLE_PREFIX."threadsread SET dateline='".TIME_NOW."' WHERE uid='{$mybb->user['uid']}' AND tid=$tid;");
        }
        else
        {
                $db->query("INSERT INTO ".TABLE_PREFIX."threadsread (tid,uid,dateline) VALUES('$tid', '{$mybb->user['uid']}', '".TIME_NOW."');");
        }

    }
    // Default back to cookie marking
    else
    {
        my_set_array_cookie("threadread", $tid, TIME_NOW, -1);
    }

    $unread_count = fetch_unread_count($fid);
    if ($unread_count == 0)
    {
        mark_forum_read($fid);
    }
}

/**
 * Fetches the number of unread threads for the current user in a particular forum.
 *
 * @param string The forums (CSV list)
 * @return int The number of unread threads
 */
function fetch_unread_count($fid)
{
	global $cache, $db, $mybb;

	$onlyview = $onlyview2 = '';
	$permissions = forum_permissions($fid);
	$cutoff = TIME_NOW-$mybb->settings['threadreadcut']*60*60*24;

	if($permissions['canonlyviewownthreads'])
	{
		$onlyview = " AND uid = '{$mybb->user['uid']}'";
		$onlyview2 = " AND t.uid = '{$mybb->user['uid']}'";
	}

    if ($mybb->user['uid'] == 0)
    {
        $comma = '';
        $tids = '';
		$threadsread = my_unserialize($mybb->cookies['mybb']['threadread']);
		$forumsread = my_unserialize($mybb->cookies['mybb']['forumread']);

		if(!empty($threadsread))
        {
            foreach ($threadsread as $key => $value)
            {
                $tids .= $comma . intval($key);
                $comma = ',';
            }
        }

        if (!empty($tids))
        {
            $count = 0;

            // We've read at least some threads, are they here?
			$query = $db->simple_select("threads", "lastpost, tid, fid", "visible=1 AND closed NOT LIKE 'moved|%' AND fid IN ($fid) AND lastpost > '{$cutoff}'{$onlyview}", array("limit" => 100));

            while ($thread = $db->fetch_array($query))
            {
                if ($thread['lastpost'] > intval($threadsread[$thread['tid']]) && $thread['lastpost'] > intval($forumsread[$thread['fid']]))
                {
                    ++$count;
                }
            }

            return $count;
        }

        // Not read any threads?
        return false;
    }
    else
    {
        
        // START - Unread posts MOD
        if (function_exists("unreadPosts_is_installed"))
        {
            $cutoff = $mybb->user['lastmark'];
        }
        // END - Unread posts MOD
        
        switch ($db->type)
        {
            case "pgsql":
                $query = $db->query("
                    SELECT COUNT(t.tid) AS unread_count
                    FROM " . TABLE_PREFIX . "threads t
                    LEFT JOIN " . TABLE_PREFIX . "threadsread tr ON (tr.tid=t.tid AND tr.uid='{$mybb->user['uid']}')
                    LEFT JOIN " . TABLE_PREFIX . "forumsread fr ON (fr.fid=t.fid AND fr.uid='{$mybb->user['uid']}')
                    WHERE t.visible=1 AND t.closed NOT LIKE 'moved|%' AND t.fid IN ($fid) AND t.lastpost > COALESCE(tr.dateline,$cutoff) AND t.lastpost > COALESCE(fr.dateline,$cutoff) AND t.lastpost>$cutoff{$onlyview2}
				");
                break;
            default:
                $query = $db->query("
                    SELECT COUNT(t.tid) AS unread_count
                    FROM " . TABLE_PREFIX . "threads t
                    LEFT JOIN " . TABLE_PREFIX . "threadsread tr ON (tr.tid=t.tid AND tr.uid='{$mybb->user['uid']}')
                    LEFT JOIN " . TABLE_PREFIX . "forumsread fr ON (fr.fid=t.fid AND fr.uid='{$mybb->user['uid']}')
					WHERE t.visible=1 AND t.closed NOT LIKE 'moved|%' AND t.fid IN ($fid) AND t.lastpost > IFNULL(tr.dateline,$cutoff) AND t.lastpost > IFNULL(fr.dateline,$cutoff) AND t.lastpost>$cutoff{$onlyview2}
				");
        }
        return $db->fetch_field($query, "unread_count");
    }
}

/**
 * Mark a particular forum as read.
 *
 * @param int The forum ID
 */
function mark_forum_read($fid)
{
	global $mybb, $db;

	// Can only do "true" tracking for registered users
	if($mybb->settings['threadreadcut'] > 0 && $mybb->user['uid'])
	{
		// Experimental setting to mark parent forums as read
		$forums_to_read = array();

		if($mybb->settings['readparentforums'])
		{
			$ignored_forums = array();
			$forums = array_reverse(explode(",", get_parent_list($fid)));

			unset($forums[0]);
			if(!empty($forums))
			{
				$ignored_forums[] = $fid;

				foreach($forums as $forum)
				{
					$fids = array($forum);
					$ignored_forums[] = $forum;

					$children = explode(",", get_parent_list($forum));
					foreach($children as $child)
					{
						if(in_array($child, $ignored_forums))
						{
							continue;
						}

						$fids[] = $child;
						$ignored_forums[] = $child;
					}

					if(fetch_unread_count(implode(",", $fids)) == 0)
					{
						$forums_to_read[] = $forum;
					}
				}
			}
		}
        $forums_to_read[] = $fid;
        $fids = implode(',', $forums_to_read);
        $insert_forums = $forums_to_read;
        $query = $db->query("SELECT fid FROM ".TABLE_PREFIX."forumsread WHERE uid='{$mybb->user['uid']}'");
        while ($forum = $db->fetch_field($query, "fid"))
        {
                unset($insert_forums[array_search($fid,$insert_forums)]);
        }
        if (count($insert_forums) > 0)
        {
                $insert_fids = implode(",{$mybb->user['uid']},".TIME_NOW."),(", $insert_forums);
                $db->query("INSERT INTO ".TABLE_PREFIX."forumsread (fid,uid,dateline) VALUES (".$insert_fids.",{$mybb->user['uid']},".TIME_NOW.");");
        }
	$db->query("UPDATE ".TABLE_PREFIX."forumsread SET dateline='".TIME_NOW."' WHERE fid IN ({$fids}) AND uid='{$mybb->user['uid']}';");
        
        // START - Unread posts MOD
//        $db->query("DELETE FROM " . TABLE_PREFIX . "threadsread WHERE (tid,uid,dateline) IN (SELECT TTR.* FROM " . TABLE_PREFIX . "threadsread AS TTR, " . TABLE_PREFIX . "threads AS TT WHERE TT.tid = TTR.tid AND TTR.uid = '" . $mybb->user['uid'] . "' AND TT.fid IN (" . $fids . "))");
        // END - Unread posts MOD
          
	}
	// Mark in a cookie
	else
	{
		my_set_array_cookie("forumread", $fid, TIME_NOW, -1);
	}
}

/**
 * Marks all forums as read.
 *
 */
function mark_all_forums_read()
{
	global $mybb, $db, $cache;

	// Can only do "true" tracking for registered users
	if($mybb->user['uid'] > 0)
	{
		$db->update_query("users", array('lastvisit' => TIME_NOW), "uid='".$mybb->user['uid']."'");
		require_once MYBB_ROOT."inc/functions_user.php";
		update_pm_count('', 2);

		if($mybb->settings['threadreadcut'] > 0)
		{
			// Need to loop through all forums and mark them as read
			$forums = $cache->read('forums');
			$fids = implode(',',array_keys($forums));
        		$insert_forums = array_keys($forums);

		        $query = $db->query("SELECT fid FROM ".TABLE_PREFIX."forumsread WHERE uid='{$mybb->user['uid']}' AND fid IN ($fids)");
		        while ($forum = $db->fetch_field($query, "fid"))
		        {
	      	        	unset($insert_forums[array_search($fid,$insert_forums)]);
	       		}
	       		if (count($insert_forums) > 0)
		        {
			        $insert_fids = implode(",{$mybb->user['uid']},".TIME_NOW."),(", $insert_forums);
	       		 	$db->query("INSERT INTO ".TABLE_PREFIX."forumsread (fid,uid,dateline) VALUES (".$insert_fids.",{$mybb->user['uid']},".TIME_NOW.");");
			}
			$db->query("UPDATE ".TABLE_PREFIX."forumsread SET dateline='".TIME_NOW."' WHERE fid IN ({$fids}) AND uid='{$mybb->user['uid']}';");
		}
	}
	else
	{
		my_setcookie("mybb[readallforums]", 1);
		my_setcookie("mybb[lastvisit]", TIME_NOW);

		my_unsetcookie("mybb[threadread]");
		my_unsetcookie("mybb[forumread]");
	}
}
?>
