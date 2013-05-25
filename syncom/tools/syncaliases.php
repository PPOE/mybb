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

$map[] = "newsletter@forum.piratenpartei.at tf-newsletter@forum.piratenpartei.at";

while ($row = $db->fetch_array($query)) {
	$u = $row['username'];
	$u = str_replace('+','%20',urlencode(strtolower($u)));
	$m = $row['email'];
        $map[] = preg_replace('/[^a-z0-9-_\.]/','', $u) . '@forum.piratenpartei.at ' . $m;
}

$query = $db->query("SELECT syncom_listname,CASE WHEN email = '' OR email ISNULL THEN email2 ELSE email END AS email FROM (SELECT A.*,B.email AS email2 FROM (SELECT B.syncom_listname,name,C.email FROM mybb_forums B LEFT JOIN mybb_moderators A ON A.fid = B.fid LEFT JOIN mybb_users C ON A.id = C.uid WHERE B.syncom_listname != '') A LEFT JOIN mybb_users B ON (A.email ISNULL OR A.email = '') AND (usergroup = 3 OR (additionalgroups NOTNULL AND additionalgroups != '' AND ('%,' || additionalgroups || ',%' LIKE '%,3,%')))) A ORDER BY syncom_listname");

$tmplist = "";
$tmpmails = "";
while ($row = $db->fetch_array($query)) {
  if (str_replace(array('@'),array('-owner@'),$row['syncom_listname']) != $tmplist)
  {
    if ($tmplist != "")
      $map[] = "$tmplist $tmpmails";
    $tmplist = str_replace(array('@'),array('-owner@'),$row['syncom_listname']);
    $tmpmails = "";
  }
  if ($tmpmails == "")
    $tmpmails = $row['email'];
  else
    $tmpmails = "$tmpmails," . $row['email'];
}
$map[] = "$tmplist $tmpmails";
file_put_contents("/etc/postfix/virtual", implode("\n", $map));
shell_exec("postmap /etc/postfix/virtual");
?>
