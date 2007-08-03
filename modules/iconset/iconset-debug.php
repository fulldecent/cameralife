<?php
  $required_images =
    array('main','small-main','topic','small-topic','album','small-album',
          'photo','small-photo','folder','small-folder',
          'login','small-login','admin','small-admin','stats','',
          'admin-item','search','icon-folder');

  if (!$_GET['theme'] || eregi('/',$_GET['theme']))
  {
    echo "Select a theme to examine<ul>";
    $fd = opendir('.');
    while ($file = readdir($fd))
      if (is_dir($file) && $file[0] != '.')
        echo "<li><a href=\"&#63;theme=$file\">$file</a>";
    echo "</ul>";
  }
  else
  {
    $theme = $_GET['theme'];

    $fd_image = opendir("$theme");
    while ($image = readdir($fd_image))
    {
      if ($image[0] == '.' || strstr($image,'~'))
        continue;
      eregi('(.*)\.(.*)',$image,$regs);
      $images[$regs[1]][] = $regs[2];
    }
    $extras = $images;

    echo "<h2>$theme - required images</h2><table width=\"100%\">";

    foreach($required_images as $image)
    {
      if ($i == 0)
        echo '<tr><td>&nbsp;<tr>';
      echo '<td align=left width="50%">';
      $i = 1-$i;

      echo "<h3>$image</h3>";
      if ($images[$image])
      {
        sort($images[$image]);

        foreach($images[$image] as $suffix)
          echo "$suffix<img src=\"$theme/$image.$suffix\"
                align=middle>&nbsp;&nbsp;&nbsp;";
      }
      else
      {
        if ($image)
          echo "<font color=red>NO IMAGES</font>";
      }

      unset($extras[$image]);
    }

    echo "</table>";
    echo "<h2>$theme - additional images</h2><table width=\"100%\">";

    foreach($extras as $image => $suffixes)
    {
      if ($i == 0)
        echo '<tr><td>&nbsp;<tr>';
      echo '<td align=left width="50%">';
      $i = 1-$i;

      echo "<h3>$image</h3>";
      if ($images[$image])
      {
        sort($images[$image]);

        foreach($images[$image] as $suffix)
          echo "$suffix<img src=\"$theme/$image.$suffix\"
                align=middle>&nbsp;&nbsp;&nbsp;";
      }
    }

    echo "</table>";
  }
?>
