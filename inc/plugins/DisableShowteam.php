<?php
	// MyBB Disable showteam.php plugin code.
	// (C) 2010 CubicleSoft.  All Rights Reserved.
	//
	// This plugin is free to use with the MyBB forum software package.
	// Disables showteam.php and dynamically removes references to it.
	// Creative Commons License:  http://creativecommons.org/licenses/by-nc-nd/3.0/us
	// Developed by Thomas Hruska, CubicleSoft Core.

	// Disallow direct access to this file for security reasons.
	if (!defined("IN_MYBB"))
	{
		die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
	}

	// Showteam hook.
	$plugins->add_hook("showteam_start", "DisableShowteam_showteam_start");

	// Template modification hooks.
	$plugins->add_hook("index_start", "DisableShowteam_index_start");
	$plugins->add_hook("memberlist_end", "DisableShowteam_memberlist_end");

	function DisableShowteam_info()
	{
		global $lang;

		$lang->load("DisableShowteam_plugin_admin");

		return Array(
			"name" => $lang->DisableShowteam_plugin_Name,
			"description" => $lang->DisableShowteam_plugin_Desc,
			"website" => "http://mods.mybboard.net/view/disable-showteam",
			"author" => $lang->DisableShowteam_plugin_Author,
			"authorsite" => "http://www.cubiclesoft.com/",
			"version" => "1.1",
			"guid" => "f4add393392fd580a0ec2f1d2ccea01b",
			"compatibility" => "14*,16*"
		);
	}

	function DisableShowteam_showteam_start()
	{
		global $lang;

		$lang->load("DisableShowteam_plugin");

		error($lang->DisableShowteam_disabled);
	}

	function DisableShowteam_index_start()
	{
		global $templates;

		// Temporarily alter the template.
		if (!is_moderator())
		{
			$content = $templates->get("index_boardstats", 0, 0);
			$findstr = '<a href="showteam.php">{$lang->forumteam}</a>';
			$content = preg_replace('/\|\s+\|/', "|", str_replace($findstr, "", $content));
			$templates->cache["index_boardstats"] = $content;
		}
	}

	function DisableShowteam_memberlist_end()
	{
		global $templates;

		// Temporarily alter the template.
		if (!is_moderator())
		{
			$content = $templates->get("memberlist", 0, 0);
			$findstr = '<a href="showteam.php"><strong>{$lang->forumteam}</strong></a>';
			$content = str_replace($findstr, "", $content);
			$templates->cache["memberlist"] = $content;
		}
	}
?>