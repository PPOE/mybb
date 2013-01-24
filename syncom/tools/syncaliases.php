<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'syncaliases.php');
define("IN_SYNCOM", 1);

// Test

$basepath = dirname($_SERVER["SCRIPT_FILENAME"]);

require_once $basepath."/../../global.php";

require MYBB_ROOT.'/syncom/config.php';

include('spyc.php');

require_once "../mybbapi.php";

$query = $db->simple_select("users", "username,email");

while ($row = $db->fetch_array($query)) {
	$u = $row['username'];
	$u = str_replace('+','%20',urlencode(strtolower($u)));
	$m = $row['email'];
        $map[] = preg_replace('/[^a-z0-9-_\.]/','', $u) . '@forum.piratenpartei.at ' . $m;
}

file_put_contents("/etc/postfix/virtual", implode("\n", $map));
exec("postmap /etc/postfix/virtual");
?>
