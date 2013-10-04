<?
$dir = opendir("/var/lib/mailman/data/");
echo "<h2>Mailinglisten mit Moderationsanfragen</h2>\n";
$files = array();
while (($file = readdir($dir)) !== false)
{
  if (strncmp($file,"heldmsg",7) == 0)
  {
    $file = preg_replace('/heldmsg-(.*)-\d+.pck/','$1',$file);
    if (strpos($file,'.') === false)
      $files[$file] = 1;
  }
}
foreach ($files as $file => $v)
{
  echo '<a href="https://forum.piratenpartei.at/cgi-bin/mailman/admin/'.$file.'">'.$file.'</a><br />';
}
closedir($dir);
?>
