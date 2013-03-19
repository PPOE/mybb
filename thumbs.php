<html>
<body>
<table>
<tr>
<th>Up</th>
<th>Down</th>
<th>Percentage</th>
<th>Name</th>
</tr>
<?
$dbconn = pg_connect("dbname=mybb")
  or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());

$data = pg_query("SELECT *,u / (1.0 * u + d) AS f,(SELECT username FROM mybb_users WHERE mybb_users.uid = A.uid) AS n FROM (SELECT uid,SUM(thumbsup) AS u,SUM(thumbsdown) AS d FROM mybb_posts GROUP BY uid ORDER BY uid) A WHERE u+d >= 50 ORDER BY f DESC;") or die('Abfrage fehlgeschlagen: ' . pg_last_error());

while ($line = pg_fetch_array($data, null, PGSQL_ASSOC))
{
  echo "<tr><td>{$line['u']}</td><td>{$line['d']}</td><td>{$line['f']}</td><td>{$line['n']}</td></tr>\n";
}

pg_free_result($data);

pg_close($dbconn);
?>
</table>
</body>
</html>
