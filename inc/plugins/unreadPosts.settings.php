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
 * Plugin Installator Class
 * 
 */
class unreadPostsInstaller
{

    public static function install()
    {
        global $db, $lang, $mybb;
        self::uninstall();

        $result = $db->simple_select('settinggroups', 'MAX(disporder) AS max_disporder');
        $max_disporder = $db->fetch_field($result, 'max_disporder');
        $disporder = 1;

        $settings_group = array(
//            'gid' => NULL,
            'name' => 'unreadPosts',
            'title' => $db->escape_string($lang->unreadPostsName),
            'description' => $db->escape_string($lang->unreadPostsGroupDesc),
            'disporder' => $max_disporder + 1,
            'isdefault' => 0
        );
        $db->insert_query('settinggroups', $settings_group);
        $gid = (int) $db->insert_id();

        $setting = array(
//            'sid' => 'NULL',
            'name' => 'unreadPostsExceptions',
            'title' => $db->escape_string($lang->unreadPostsExceptions),
            'description' => $db->escape_string($lang->unreadPostsExceptionsDesc),
            'optionscode' => 'text',
            'value' => '',
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        $setting = array(
//            'sid' => 'NULL',
            'name' => 'unreadPostsStatusActionUnread',
            'title' => $db->escape_string($lang->unreadPostsStatusActionUnread),
            'description' => $db->escape_string($lang->unreadPostsStatusActionUnreadDesc),
            'optionscode' => 'onoff',
            'value' => '1',
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        $setting = array(
//            'sid' => 'NULL',
            'name' => 'unreadPostsStatusPostbitMark',
            'title' => $db->escape_string($lang->unreadPostsStatusPostbitMark),
            'description' => $db->escape_string($lang->unreadPostsStatusPostbitMarkDesc),
            'optionscode' => 'onoff',
            'value' => '1',
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        $setting = array(
//            'sid' => 'NULL',
            'name' => 'unreadPostsStatusCounter',
            'title' => $db->escape_string($lang->unreadPostsStatusCounter),
            'description' => $db->escape_string($lang->unreadPostsStatusCounterDesc),
            'optionscode' => 'onoff',
            'value' => '1',
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        $setting = array(
//            'sid' => 'NULL',
            'name' => 'unreadPostsStatusCounterHide',
            'title' => $db->escape_string($lang->unreadPostsStatusCounterHide),
            'description' => $db->escape_string($lang->unreadPostsStatusCounterHideDesc),
            'optionscode' => 'onoff',
            'value' => '0',
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        $setting = array(
//            'sid' => 'NULL',
            'name' => 'unreadPostsCounterPages',
            'title' => $db->escape_string($lang->unreadPostsCounterPages),
            'description' => $db->escape_string($lang->unreadPostsCounterPagesDesc),
            'optionscode' => 'textarea',
            'value' => 'index.php',
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        $setting = array(
//            'sid' => 'NULL',
            'name' => 'unreadPostsMarkAllReadLink',
            'title' => $db->escape_string($lang->unreadPostsMarkAllReadLink),
            'description' => $db->escape_string($lang->unreadPostsMarkAllReadLinkDesc),
            'optionscode' => 'onoff',
            'value' => '1',
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);
        
        $setting = array(
//            'sid' => 'NULL',
            'name' => 'unreadPostsMarkerStyle',
            'title' => $db->escape_string($lang->unreadPostsMarkerStyle),
            'description' => $db->escape_string($lang->unreadPostsMarkerStyleDesc),
            'optionscode' => 'textarea',
            'value' => "color:red;\nfont-weight:bold;",
            'disporder' => $disporder++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        // Add last mark field - time when user mark all forums read
        if (!$db->field_exists("lastmark", "users"))
        {
            $db->add_column("users", "lastmark", "INT NOT NULL DEFAULT '0'");
        }

        $db->update_query("users", array("lastmark" => "regdate"), '', '', true);
        $db->update_query("settings", array("value" => "365"), "name = 'threadreadcut'");
    }

    public static function uninstall()
    {
        global $db;

        $db->delete_query('settinggroups', "name = 'unreadPosts'");
        $db->delete_query('settings', "name = 'unreadPostsExceptions'");
        $db->delete_query('settings', "name = 'unreadPostsStatusCounter'");
        $db->delete_query('settings', "name = 'unreadPostsStatusCounterHide'");
        $db->delete_query('settings', "name = 'unreadPostsCounterPages'");
        $db->delete_query('settings', "name = 'unreadPostsStatusActionUnread'");
        $db->delete_query('settings', "name = 'unreadPostsStatusPostbitMark'");
        $db->delete_query('settings', "name = 'unreadPostsMarkAllReadLink'");
        $db->delete_query('settings', "name = 'unreadPostsMarkerStyle'");

        $db->update_query("settings", array("value" => "7"), "name = 'threadreadcut'");
    }

}
?>
