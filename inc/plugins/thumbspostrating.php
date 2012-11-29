<?php
/**
 *  Plugin  : Thumbs Post Rating
 *  Author  : TY Yew
 *  Version : 1.3
 *  Website : http://tyyew.com/mybb
 *  Contact : mybb@tyyew.com
 *
 *  This file is part of Thumbs Post Rating plugin for MyBB.
 *
 *  Thumbs Post Rating plugin for MyBB is free software; you can
 *  redistribute it and/or modify it under the terms of the GNU General
 *  Public License as published by the Free Software Foundation; either
 *  version 3 of the License, or (at your option) any later version.
 *
 *  Thumbs Post Rating plugin for MyBB is distributed in the hope that it
 *  will be useful, but WITHOUT ANY WARRANTY; without even the implied
 *  warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See
 *  the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http:www.gnu.org/licenses/>.
 */

// No direct initiation
if( !defined('IN_MYBB') )
{
	die('Direct initialization of this file is not allowed.');
}

// Add hooks
$plugins->add_hook('global_start','tpr_global');
$plugins->add_hook('postbit','tpr_box');
$plugins->add_hook('xmlhttp','tpr_action');
$plugins->add_hook('class_moderation_delete_post','tpr_deletepost');
$plugins->add_hook('class_moderation_delete_thread_start','tpr_deletethread');
$plugins->add_hook('class_moderation_delete_thread','tpr_deletethread2');

// Plugin information
function thumbspostrating_info()
{
	global $lang;
	$lang->load('thumbspostrating');

	return array(
		'name' => $lang->tpr_info_name,
		'description' => $lang->tpr_info_desc,
		'website' => 'http://community.mybb.com/thread-84250.html',
		'author' => 'TY Yew',
		'authorsite' => 'http://community.mybb.com/user-30826.html',
		'version' => '1.3',
		'guid' => '21de27b859c0095ec17f86f561fa3737',
		'compatibility' => '14*,15*,16*'
	);
}

// Install function
function thumbspostrating_install()
{
	global $db;

	@ignore_user_abort(true);
	@set_time_limit(600);
	$db->write_query('ALTER TABLE '.TABLE_PREFIX.'posts ADD `thumbsup` INT UNSIGNED NOT NULL DEFAULT 0, ADD `thumbsdown` INT UNSIGNED NOT NULL DEFAULT 0', true);
	$db->write_query('CREATE TABLE '.TABLE_PREFIX.'thumbspostrating (
		id SERIAL ,
		pid INT NOT NULL ,
		uid INT NOT NULL ,
		thumbsup INT NOT NULL ,
		thumbsdown INT NOT NULL ,
		PRIMARY KEY ( id )
		);'
	);
}

// Activate function
function thumbspostrating_activate()
{
	global $db, $lang;
	$lang->load('thumbspostrating');

	require MYBB_ROOT.'/inc/adminfunctions_templates.php';

	find_replace_templatesets('postbit','#'.preg_quote('<div class="post_body" id="pid_{$post[\'pid\']}">').'#','<div class="float_right">{$post[\'tprdsp\']}</div><div class="post_body" id="pid_{$post[\'pid\']}">');
	find_replace_templatesets('postbit_classic','#'.preg_quote('{$post[\'message\']}').'#','<div class="float_right">{$post[\'tprdsp\']}</div>{$post[\'message\']}');

	$tpr_setting_group_1 = array(
		'name' => 'tpr_group',
		'title' => $db->escape_string($lang->tpr_set_group_1_title),
		'description' => $db->escape_string($lang->tpr_set_group_1_desc),
		'disporder' => '38',
		'isdefault' => 0
	);
	$db->insert_query('settinggroups',$tpr_setting_group_1);
	$gid = $db->insert_id();

	$disporder = 0;
	foreach(array(
		'usergroups' => array('text', '2,3,4,6'),
		'forums' => array('text', '0'),
		'selfrate'  => array('yesno', 1),
		'undorate' => array('yesno', 1),
		'closerate' => array('yesno', 1),
	) as $name => $opts)
	{
		$lang_title = 'tpr_set_'.$name.'_title';
		$lang_desc = 'tpr_set_'.$name.'_desc';
		$db->insert_query('settings', array(
			'name' => 'tpr_'.$name,
			'title' => $db->escape_string($lang->$lang_title),
			'description' => $db->escape_string($lang->$lang_desc),
			'optionscode' => $opts[0],
			'value' => $db->escape_string($opts[1]),
			'disporder' => ++$disporder,
			'gid' => 0,
		));
	}

	rebuild_settings();
}

// Deactivate function
function thumbspostrating_deactivate()
{
	global $db;

	$gid = $db->fetch_field($db->simple_select('settinggroups','gid','name="tpr_group"'),'gid');
	if($gid)
	{
		$db->delete_query('settings','gid='.$gid);
		$db->delete_query('settinggroups','gid='.$gid);
	}
	rebuild_settings();

	require MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('postbit','#'.preg_quote('<div class="float_right">{$post[\'tprdsp\']}</div>').'#','');
	find_replace_templatesets('postbit_classic','#'.preg_quote('<div class="float_right">{$post[\'tprdsp\']}</div>').'#','');	
}

// Is Installed function
function thumbspostrating_is_installed()
{
	global $db;

	if($db->table_exists('thumbspostrating'))
	{
		return true;
	}
	return false;
}

// Uninstall function
function thumbspostrating_uninstall()
{
	global $db;

	@ignore_user_abort(true);
	@set_time_limit(600);
	$db->write_query('ALTER TABLE '.TABLE_PREFIX.'posts DROP thumbsup, DROP thumbsdown', true);
	$db->write_query('DROP TABLE '.TABLE_PREFIX.'thumbspostrating');
}

// Function - Load TPR template and style only in showthread.php
function tpr_global()
{
	if($GLOBALS['current_page'] != 'showthread.php') return;
	$GLOBALS['templatelist'] .= ',postbit_tpr';
}

// Function - Forum exclusion
function tpr_forum($fidcheck)
{
	global $mybb;
	
	$fidset =& $mybb->settings['tpr_forums'];
	if($fidset != 0)
	{
		foreach(array_map('trim', explode(',',$fidset)) as $fid)
		{
			if( $fid == $fidcheck )
			{
				return false;
			}
		}
	}
	return true;
}

// Function - Usergroup checking
function grpchk($setting)
{
	global $mybb;
	$user = $mybb->user;

	$set = array_map('trim',explode(',',$mybb->settings['tpr_'.$setting]));

	if($set != 0)
	{
		foreach($set as $grp)
		{
			if($grp == $user['usergroup'])
			{
				$result = true;
			}
		}
	}

	if($user['additionalgroups'])
	{
		$addgrp = array_map('trim',explode(',',$user['additionalgroups']));
		$com = array_intersect($addgrp, $set);
		if (!empty($com))
		{
			$result = true;
		}
	}	

	if($result != true) return false;

	return true;
}

// Function - Check whether user can rate (Usergroup, Self rating, Closed thread)
function tpr_canrate($postuid)
{
	global $mybb;

	$user =& $mybb->user;

	// Guests
	if(!$user['uid']) return false;

	// Usergroup permission
	if(!grpchk('usergroups')) return false;

	// Self-rating
	if($postuid == $user['uid'] && $mybb->settings['tpr_selfrate'] == 1) return false;

	// Rate close thread
	if($GLOBALS['thread']['closed'] && $mybb->settings['tpr_closerate'] != 1) return false;

	return true;
}

// Function - Display ratebox
function tpr_box(&$post)
{
	global $db, $mybb, $templates, $lang, $current_page, $box_view, $tprdsp;

	$tid = (int) $post['tid'];
	$pid = (int) $post['pid'];
	$postuid = (int) $post['uid'];
	$uid = $mybb->user['uid'];
	$user = $mybb->user;
	$fid = $post['fid'];
	if(!$pid) return;

	// Remove hook if forum is excluded from TPR
	if(!tpr_forum($post['fid']))
	{
		global $plugins;
		$plugins->remove_hook('postbit', 'tpr_box');
		return;
	}

	// Stick in JS and CSS
	$GLOBALS['headerinclude'] .= '<script type="text/javascript" src="'.$mybb->settings['bburl'].'/jscripts/thumbspostrating.js?ver=1600"></script><link type="text/css" rel="stylesheet" href="'.$mybb->settings['bburl'].'/css/thumbspostrating.css" />';

	// Build user rating cache
	static $done_init = false;
	static $user_ru = null;
	static $user_rd = null;
	if(!$done_init)
	{
		$done_init = true;
		
		$user_ru = array();
		$user_rd = array();
		
		if($current_page == 'showthread.php')
		{
			$lang->load('thumbspostrating');
			
			// If user is not guest, check for user ratings
			if($mybb->user['uid'])
			{

                // Lets get the pids
                $pids = array();
                $query = $db->simple_select("posts p", "p.pid", "p.tid='$tid' $visible", array('order_by' => 'p.dateline', 'limit_start' => $start, 'limit' => $perpage));
                while($getid = $db->fetch_array($query))
                {
                        $pids[] = intval($getid['pid']);
                }
		$pids2 = "";
                if($pids)
                {
                        $pids2 = " AND pid IN (" . implode(",", $pids) . ")";
		}

				$query = $db->simple_select('thumbspostrating', 'pid,thumbsup,thumbsdown', 'uid='.$mybb->user['uid'].$pids2);
			
				while($ttrate = $db->fetch_array($query))
				{
					$user_ru[$ttrate['pid']] = $ttrate['thumbsup'];
					$user_rd[$ttrate['pid']] = $ttrate['thumbsdown'];
				}

				$db->free_result($query);
			}
		}
	}

	// If user already rated...
	if ($user_ru[$pid] == 1 xor $user_rd[$pid] == 1)
	{
		$userrated[$pid] = true;
		// Make thumbs for user who rated up
		if ($user_ru[$pid] == 1)
		{
			$tu_img = '<div class="tpr_thumb tu1"></div>';
			$td_img = '<div class="tpr_thumb td0"></div>';
		}
		// Make thumbs for user who rated down
		else
		{
			$tu_img = '<div class="tpr_thumb tu0"></div>';
			$td_img = '<div class="tpr_thumb td1"></div>';
		}
	}
	else
	{
		$userrated[$pid] = false;
	}

	// Check whether the user can rate
	if (!tpr_canrate($postuid))
	{
		$cantrate = true;
	}

	$tu_url = $mybb->settings['bburl'].'/xmlhttp.php?action=tpr&amp;my_post_key='.$mybb->post_code.'&amp;pid='.$pid.'&amp;tu=1&amp;td=0';
	$td_url = $mybb->settings['bburl'].'/xmlhttp.php?action=tpr&amp;my_post_key='.$mybb->post_code.'&amp;pid='.$pid.'&amp;tu=0&amp;td=1';
	$un_url = $mybb->settings['bburl'].'/xmlhttp.php?action=tpr&amp;my_post_key='.$mybb->post_code.'&amp;pid='.$pid.'&amp;tu=0&amp;td=0';

	// If user have not yet rated (correctly)...
	if ($userrated[$pid] != true)
	{
		// Fail-safe (force user who incorectly rated to be unable to rate)
		if (isset($user_ru[$pid]) || isset($user_rd[$pid]))
		{
			if ($user_ru[$pid] != 1 xor $user_rd[$pid] != 1)
			{
				return;
			}
			else
			{
				$cantrate = true;
			}
		}

		// Make thumbs for user who cannot rate
		if ($cantrate == true)
		{
			$tu_img = '<div class="tpr_thumb tu0"></div>';
			$td_img = '<div class="tpr_thumb td0"></div>';
		}
		// Make thumbs for user who can rate
		else
		{
			$tu_img = '<a href="'.$tu_url.'" class="tpr_thumb tu2" title="'.$lang->tpr_rate_up.'" onclick="return thumbRate(1,0,'.$pid.')" ></a>';
			$td_img = '<a href="'.$td_url.'" class="tpr_thumb td2" title="'.$lang->tpr_rate_down.'" onclick="return thumbRate(0,1,'.$pid.')" ></a>';
			
			// Respect MyBB's wish to disable xmlhttp
			if($mybb->settings['use_xmlhttprequest'] == 0)
			{
				$tu_img = str_replace('onclick="return thumbRate', 'rel="', $tu_img);
				$td_img = str_replace('onclick="return thumbRate', 'rel="', $td_img);
			}
		}
	}

	// Display number of thumbs
	$tu_no = $post['thumbsup'];
	$td_no = $post['thumbsdown'];

	// Display undo rating?
	if($mybb->settings['tpr_undorate'] == 1 && $userrated[$pid] == true && !$cantrate)
	{
		$box_remove = '<a href='.$un_url.' onclick="return thumbRate(0,0,'.$pid.')" >'.$lang->tpr_undo_rate.'</a><br />';

		// Respect MyBB's wish to disable xmlhttp
		if($mybb->settings['use_xmlhttprequest'] == 0)
		{
			$box_remove = str_replace('onclick="return thumbRate', 'rel="', $box_remove);
		}
	}

	// Make the box
	$box = <<<BOX
<table class="tpr_box" id="tpr_stat_$pid">
	<tr>
		<td class="tu_stat" id="tu_stat_$pid">$tu_no</td>
		<td>$tu_img</td>
		<td>$td_img</td>
		<td class="td_stat" id="td_stat_$pid">$td_no</td>
	</tr>
	<tr>
		<td class="small" colspan="4" >
			<span id="tpr_remove_$pid">$box_remove</span>
			$box_view
		</td>
	</tr>
</table>
BOX;

$post['tprdsp'] = $box;
}

// Function - Thumbs Action
function tpr_action()
{
	global $mybb, $db, $tid, $lang;

	// Return if action is not TPR
	if ($mybb->input['action'] != 'tpr' ) return;

	$uid = $mybb->user['uid'];
	$pid = (int)$mybb->input['pid'];
	$tu = (int)$mybb->input['tu'];
	$td = (int)$mybb->input['td'];
	$lang->load('thumbspostrating');

	// Check whether the rating is valid
	if (($tu == 1 && $td == 0) || ($tu == 0 && $td == 1) || ($tu == 0 && $td == 0))
	{
		// Check whether the post key is valid
		if (!verify_post_check($mybb->input['my_post_key'], true)) xmlhttp_error($lang->invalid_post_code);
	}
	else
	{
		xmlhttp_error($lang->tpr_error_invalid_rating);
	}

	$post = get_post($pid);
	$thread = get_thread($post['tid']);
	$forumpermissions = forum_permissions($post['fid']);

	// Check whether the post exist and is visible
	if(!$post['pid']) xmlhttp_error($lang->post_doesnt_exist);
	if(($thread['visible'] != 1 || $post['visible'] != 1) && !$ismod) xmlhttp_error($lang->post_doesnt_exist);

	// Check whether the user can rate
	$postuid = (int) $post['uid'];
	if (!tpr_canrate($postuid)) xmlhttp_error($lang->tpr_error_cannot_rate);
	
	// Check whether the user can view the post
	if($forumpermissions['canview'] != 1 || $forumpermissions['canviewthreads'] != 1) xmlhttp_error($lang->post_doesnt_exist);
	if($forumpermissions['canonlyviewownthreads'] == 1 && $thread['uid'] != $mybb->user['uid']) xmlhttp_error($lang->post_doesnt_exist);

	// Check whether the user has rated
	$rated = $db->fetch_array($db->simple_select('thumbspostrating','thumbsup,thumbsdown','uid='.$uid.' and pid='.$pid));
	if(($rated['thumbsup'] == 1 || $rated['thumbsdown'] == 1) && ($tu == 1 || $td == 1)) xmlhttp_error($lang->tpr_error_already_rated);

	// Action for user who rated thumbs up
	if($tu == 1 && $td == 0)
	{
		$insert_thumbs = array(
			'thumbsup' => 1,
			'uid' => $uid,
			'pid' => $pid
	);

		// Insert the data into database
		$db->insert_query('thumbspostrating',$insert_thumbs);
		$db->write_query('UPDATE '.TABLE_PREFIX.'posts SET thumbsup = thumbsup +1 WHERE pid='.$pid);

		++$post['thumbsup'];
	}
	// Action for user who rated thumbs down
	elseif($tu == 0 && $td == 1)
	{
		$insert_thumbs = array(
			'thumbsdown' => 1,
			'uid' => $uid,
			'pid' => $pid
		);

		// Insert the data into database
		$db->insert_query('thumbspostrating',$insert_thumbs);
		$db->write_query('UPDATE '.TABLE_PREFIX.'posts SET thumbsdown = thumbsdown +1 WHERE pid='.$pid);
		
		++$post['thumbsdown'];
	}
	// Action for user who undo rating
	elseif($tu == 0 && $td == 0)
	{
		if ($rated['thumbsup'] == 1)
		{
			$db->write_query('UPDATE '.TABLE_PREFIX.'posts SET thumbsup = thumbsup -1 WHERE pid='.$pid);
			--$post['thumbsup'];
		}
		elseif ($rated['thumbsdown'] == 1)
		{
			$db->write_query('UPDATE '.TABLE_PREFIX.'posts SET thumbsdown = thumbsdown -1 WHERE pid='.$pid);
			--$post['thumbsdown'];
		}
		else
		{
			return;
		}

		$db->delete_query('thumbspostrating', 'uid='.$uid.' and pid='.$pid);
	}
	// Error if none of the above
	else
	{
		xmlhttp_error($lang->tpr_error_invalid_rating);
	}

	// Feedback to the user
	if(!$mybb->input['ajax'])
	{
		header('Location: '.htmlspecialchars_decode(get_post_link($pid, $post['tid'])).'#pid'.$pid);
	}
	else
	{
		// Push new values to client	
		echo 'success||', $post['pid'], '||', (int)$post['thumbsup'], '||', (int)$post['thumbsdown'];
		
		// What user has just rated?
		if ($tu == 1 && $td == 0)
		{
			echo '||1';
		}
		elseif ($tu == 0 && $td == 1)
		{
			echo '||-1';
		}
		elseif ($tu == 0 && $td == 0)
		{
			echo '||0';
		}

		// Show undo rating if enabled
		if($mybb->settings['tpr_undorate'] == 1 && ($tu || $td) == 1)
		{
			echo '||show_undo';
		}

		// Remove undo rating if undone
		elseif($mybb->settings['tpr_undorate'] == 1 && ($tu && $td) == 0)
		{
			echo '||hide_undo';
		}
	}
}

// Function - Delete post
function tpr_deletepost($pid)
{
	global $db;
	$db->delete_query('thumbspostrating', 'pid='.$pid);
}

// Function - Delete thread
function tpr_deletethread($tid)
{
	global $db, $tpr_pids;
	// grab pids, but only delete later
	$tpr_pids = '';
	$query = $db->simple_select('posts', 'pid', 'tid='.$tid);
	while($pid = $db->fetch_field($query, 'pid'))
		$tpr_pids .= ($tpr_pids ? ',':'') . $pid;
	$db->free_result($query);
}

// Function - Delete thread 2
function tpr_deletethread2($tid)
{
	global $tpr_pids, $db;
	if(!$tpr_pids) return;
	
	$db->delete_query('thumbspostrating', 'pid IN ('.$tpr_pids.')');
	$tpr_pids = '';
}
?>
