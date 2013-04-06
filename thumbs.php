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

$data = pg_query("SELECT *,u / (1.0 * u + d) AS f FROM (SELECT username,SUM(thumbsup) AS u,SUM(thumbsdown) AS d FROM mybb_posts GROUP BY username ORDER BY username) A WHERE u+d >= 10 ORDER BY f DESC;") or die('Abfrage fehlgeschlagen: ' . pg_last_error());

while ($line = pg_fetch_array($data, null, PGSQL_ASSOC))
{
  echo "<tr><td>{$line['u']}</td><td>{$line['d']}</td><td>{$line['f']}</td><td>{$line['username']}</td></tr>\n";
}

pg_free_result($data);

pg_close($dbconn);
?>
</table>
</body>
</html>
