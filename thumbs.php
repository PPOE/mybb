<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><!-- start: showthread -->
<html xml:lang="de" lang="de" xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Forum Thumbs</title>
<script type="text/javascript" src="sorttable.js"></script>
<link rel="alternate" type="application/rss+xml" title="Letzte Themen (RSS 2.0)" href="https://forum.piratenpartei.at/syndication.php" />
<link rel="alternate" type="application/atom+xml" title="Letzte Themen (Atom 1.0)" href="https://forum.piratenpartei.at/syndication.php?type=atom1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<script type="text/javascript" src="https://forum.piratenpartei.at/jscripts/prototype.js?ver=1603"></script>
<script type="text/javascript" src="https://forum.piratenpartei.at/jscripts/general.js?ver=1603"></script>
<script type="text/javascript" src="https://forum.piratenpartei.at/jscripts/popup_menu.js?ver=1600"></script>
<link type="text/css" rel="stylesheet" href="https://forum.piratenpartei.at/cache/themes/theme5/global.css" />
<link type="text/css" rel="stylesheet" href="https://forum.piratenpartei.at/cache/themes/theme5/showthread.css" />
<link type="text/css" rel="stylesheet" href="https://forum.piratenpartei.at/cache/themes/theme1/star_ratings.css" />
</head>
<body>
<div id="container">
<table border="0" cellspacing="1" cellpadding="2" class="sortable" style="border-top-width: 0;">
<tr>
<th class="trow2">Up</th>
<th class="trow2">Down</th>
<th class="trow2">Thumbs/Post</th>
<th class="trow2">Posts</th>
<th class="trow2">Percentage</th>
<th class="trow2">Name</th>
<th class="trow2">Ignoriert von</th>
</tr>
<?
$dbconn = pg_connect("dbname=mybb")
  or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());

$min = isset($_GET['min']) ? intval($_GET['min']) : 10;
$min = $min < 10 ? 10 : $min;

$data = pg_query("SELECT U.username, COUNT(P.pid) AS count, thumbs_up AS u,thumbs_down AS d, thumbs_up / (1.0 * thumbs_up + thumbs_down) AS f,(SELECT COUNT(*) FROM mybb_users V WHERE (V.usergroup = 9 OR ',' || V.additionalgroups || ',' LIKE '%,9,%') AND (',' || V.ignorelist || ',' LIKE '%,' || U.uid || ',%')) AS ignoredbypithumbs,(SELECT COUNT(*) FROM mybb_users V WHERE (',' || V.ignorelist || ',' LIKE '%,' || U.uid || ',%')) AS ignoredbyall FROM mybb_users U LEFT JOIN mybb_posts P ON P.uid = U.uid WHERE thumbs_up+thumbs_down >= $min GROUP BY U.username, U.thumbs_up, U.thumbs_down, U.uid, P.uid ORDER BY f DESC;") or die('Abfrage fehlgeschlagen: ' . pg_last_error());

$trow = 1;
while ($line = pg_fetch_array($data, null, PGSQL_ASSOC))
{
  echo "<tr><td class=\"trow$trow\">{$line['u']}</td><td class=\"trow$trow\">{$line['d']}</td><td class=\"trow$trow\">".round(($line['u'] + $line['d']) / $line['count'],2)."</td><td class=\"trow$trow\">{$line['count']}</td><td class=\"trow$trow\">".round($line['f'] * 100,2)."</td><td class=\"trow$trow\">{$line['username']}</td><td class=\"trow$trow\">{$line['ignoredbyall']} Benutzern, davon {$line['ignoredbypirates']} Piraten</td></tr>\n";
  $trow = ($trow) % 2 + 1;
}

pg_free_result($data);

pg_close($dbconn);
?>
</table>
</div>
</body>
</html>
