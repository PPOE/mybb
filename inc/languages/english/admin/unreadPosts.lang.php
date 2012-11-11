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

$l['unreadPostsName'] = 'View Unread Posts';
$l['unreadPostsDesc'] = 'This plugin add a "view unread posts" function for all registered users.';
$l['unreadPostsGroupDesc'] = 'Settings for plugin "View Unread Posts"';


$l['unreadPostsExceptions'] = 'Exception forums list';
$l['unreadPostsExceptionsDesc'] = 'Forums IDs which will not be searched, comma seprated';

$l['unreadPostsStatusActionUnread'] = 'Change lastpost to first unread post topic links';
$l['unreadPostsStatusActionUnreadDesc'] = 'This option replaces all the links on the forum lead to the last post (action = lastpost) for links to first unread post in the thread.';

$l['unreadPostsStatusPostbitMark'] = 'Unread posts marker in view topic';
$l['unreadPostsStatusPostbitMarkDesc'] = 'If enabled, unread posts in threads will marked with data from the template unreadPosts_postbit (default string)';

$l['unreadPostsStatusCounter'] = 'Unread posts counter';
$l['unreadPostsStatusCounterDesc'] = 'Add a unread posts counter near the link.';

$l['unreadPostsStatusCounterHide'] = 'Hide "view unread posts" link, when there are no unread';
$l['unreadPostsStatusCounterHideDesc'] = 'This option hides url for searching unread posts, when there are not unread posts for user. Works only then "Unread posts counter" is enabled.';

$l['unreadPostsCounterPages'] = 'Subpages with active unread posts counter posts';
$l['unreadPostsCounterPagesDesc'] = 'Pages-codes (THIS_SCRIPT constant), on which the unread posts counter will be active active. If not specified, the counter will be active on all pages.';

$l['unreadPostsMarkAllReadLink'] = 'Show "Mark all threads read" link in search results';
$l['unreadPostsMarkAllReadLinkDesc'] = 'If enabled, "mark all threads read" link will be displayed above the search results.';

$l['unreadPostsMarkerStyle'] = 'Unread posts marker style';
$l['unreadPostsMarkerStyleDesc'] = 'CSS style for unread posts marker in thread view.';

?>