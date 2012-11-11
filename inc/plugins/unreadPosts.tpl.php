<?php
/**
 *
 * @author Lukasz "LukasAMD" Tkacz
 *
 * @package Unread Posts
 * @version 2.7
 * @copyright (c) Lukasz Tkacz
 * @license Based on CC BY-NC-SA 3.0 with special clause
 *
 */
 
/**
 * Disallow direct access to this file for security reasons
 * 
 */
if (!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

/**
 * Plugin Activator Class
 * 
 */
class unreadPostsActivator
{

    private static $tpl = array();

    private static function getTpl()
    {
        global $db;

        self::$tpl[] = array(
//            "tid" => NULL,
            "title" => 'unreadPosts_link',
            "template" => $db->escape_string('
 | <a href="{$mybb->settings[\'bburl\']}/search.php?action=unreads">{$lang->unreadPostsLink}</a>'),
            "sid" => "-1",
            "version" => "1.0",
            "dateline" => TIME_NOW,
        );

        self::$tpl[] = array(
//            "tid" => NULL,
            "title" => 'unreadPosts_linkCounter',
            "template" => $db->escape_string('
 | <a href="{$mybb->settings[\'bburl\']}/search.php?action=unreads">{$lang->unreadPostsLink} {$unreadPostsCounter}</a>'),
            "sid" => "-1",
            "version" => "1.0",
            "dateline" => TIME_NOW,
        );

        self::$tpl[] = array(
//            "tid" => NULL,
            "title" => 'unreadPosts_counter',
            "template" => $db->escape_string('
({$numUnreads})'),
            "sid" => "-1",
            "version" => "1.0",
            "dateline" => TIME_NOW,
        );

        self::$tpl[] = array(
//            "tid" => NULL,
            "title" => 'unreadPosts_postbit',
            "template" => $db->escape_string('
<span class="post_unread_marker">{$lang->unreadPostsMarker}</span>'),
            "sid" => "-1",
            "version" => "1.0",
            "dateline" => TIME_NOW,
        );

        self::$tpl[] = array(
//            "tid" => NULL,
            "title" => 'unreadPosts_markAllReadLink',
            "template" => $db->escape_string('
<td align="left" valign="top"><a href="misc.php?action=markread{$post_code_string}" "class="smalltext">{$lang->unreadPostsMarkAllRead}</a></td>'),
            "sid" => "-1",
            "version" => "1.0",
            "dateline" => TIME_NOW,
        );
    }

    public static function activate()
    {
        global $db;
        self::deactivate();

        for ($i = 0; $i < sizeof(self::$tpl); $i++)
        {
            $db->insert_query('templates', self::$tpl[$i]);
        }
        find_replace_templatesets('header_welcomeblock_member', '#' . preg_quote('{$lang->welcome_todaysposts}</a>') . '#', '{$lang->welcome_todaysposts}</a><!-- UNREADPOSTS_LINK -->');
        find_replace_templatesets('postbit_posturl', '#' . preg_quote('<strong>') . '#', '<!-- IS_UNREAD --><strong>');

        find_replace_templatesets('search_results_posts', '#' . preg_quote('<td align="right" valign="top">{$multipage}') . '#', '<!-- UNREADPOSTS_MARKALL --><td align="right" valign="top">{$multipage}');
        find_replace_templatesets('search_results_threads', '#' . preg_quote('<td align="right" valign="top">{$multipage}') . '#', '<!-- UNREADPOSTS_MARKALL --><td align="right" valign="top">{$multipage}');
        
        find_replace_templatesets('showthread', '#' . preg_quote('{$headerinclude}') . '#', '{$headerinclude}<!-- UNREADPOSTS_CSS -->');
    }

    public static function deactivate()
    {
        global $db;
        self::getTpl();

        for ($i = 0; $i < sizeof(self::$tpl); $i++)
        {
            $db->delete_query('templates', "title = '" . self::$tpl[$i]['title'] . "'");
        }

        require_once(MYBB_ROOT . '/inc/adminfunctions_templates.php');
        find_replace_templatesets('header_welcomeblock_member', '#' . preg_quote('<!-- UNREADPOSTS_LINK -->') . '#', '');
        find_replace_templatesets('postbit_posturl', '#' . preg_quote('<!-- IS_UNREAD -->') . '#', '');

        find_replace_templatesets('search_results_posts', '#' . preg_quote('<!-- UNREADPOSTS_MARKALL -->') . '#', '');
        find_replace_templatesets('search_results_threads', '#' . preg_quote('<!-- UNREADPOSTS_MARKALL -->') . '#', '');
        
        find_replace_templatesets('showthread', '#' . preg_quote('<!-- UNREADPOSTS_CSS -->') . '#', '');
    }
}
?>
