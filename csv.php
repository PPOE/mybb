<html>
<body>
<?php
if ($_FILES["uploadedfile"] != null)
{
  echo "<h2>".$_FILES["uploadedfile"]["name"]."</h2><br><br>\n<table border=1>\n";
  $handle = fopen($_FILES["uploadedfile"]["tmp_name"], "r");
  $c = 0;
  while (($data = fgetcsv($handle, max(0,min(100000,$_FILES["uploadedfile"]["size"])), ",")) !== FALSE) {
    $c++;
    if ($c < 4)
      continue;
    echo "<tr>\n";
    $i = 0;
    foreach ($data as $col)
    {
      $i++;
      if ($i == 7 || $i == 9 || $i == 12 || $i == 14 || $i == 16 || $i == 19)
        continue;
      echo "<td>\n";
        echo $col."&nbsp;\n";
      echo "</td>\n";
    }
    echo "</tr>\n";
  }
  fclose($handle);
}
echo '
<form enctype="multipart/form-data" action="csv.php" method="POST">
<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
Monat (2 Ziffern): <input type="text" size="20"></input>
Jahr (4 Ziffern): <input type="text" size="20"></input>
CSV Datei: <input name="uploadedfile" type="file" /><br />
<input type="submit" value="Upload File" />
</form>
';
?>
</body>
</html>
