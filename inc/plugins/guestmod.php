<?php
/*
	Plugin:		Guest's post to moderation
	Version:	0.1.1
	Author:		Tomasz  Knapik
	Date:		20.05.2011
*/

if (!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Add hooks
$plugins->add_hook("datahandler_post_insert_post", "guestmod_datahandler_post_insert_post");
$plugins->add_hook("datahandler_post_insert_thread_post", "guestmod_datahandler_post_insert_thread_post");
$plugins->add_hook("datahandler_post_insert_thread", "guestmod_datahandler_post_insert_thread");

// Stanard MyBB function with informations about plugin
function guestmod_info()
{
	return Array(
		'name' => 'Guest\'s post to moderation',
		'author' => 'Tomasz Knapik',
		'authorsite' => 'http://community.mybb.com/user-44546.html',
		'version' => '0.1.1',
		'compatibility' => '16*'
	);
}

//Functions
function guestmod_datahandler_post_insert_post($it)
{
	global $mybb,$post;
	if($mybb->user['usergroup']==1 || $mybb->user['usergroup'] == 5 || ($mybb->user['usergroup'] == 2 && 1 == 0))
	{
		$it->post_insert_data['visible']=0;
	}
}

function guestmod_datahandler_post_insert_thread_post($it)
{
	global $mybb,$post;
	if($mybb->user['usergroup']==1 || $mybb->user['usergroup'] == 5 || ($mybb->user['usergroup'] == 2 && 1 == 0))
	{
		$it->post_insert_data['visible']=0;
	}
}

function guestmod_datahandler_post_insert_thread($it)
{
	global $mybb,$post;
	if($mybb->user['usergroup']==1 || $mybb->user['usergroup'] == 5 || ($mybb->user['usergroup'] == 2 && 1 == 0))
	{
		$it->thread_insert_data['visible']=0;
	}
}
?>
