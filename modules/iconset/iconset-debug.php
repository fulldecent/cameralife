<?php
/**
 * Validates an installed IconSet
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

$required_images =
  array('main','small-main','topic','small-topic','album','small-album',
        'photo','small-photo','folder','small-folder',
        'login','small-login','admin','small-admin','stats','small-stats',
        'admin-item','search','small-search','icon-folder');

if (!$_GET['theme'] || eregi('[/\\]',$_GET['theme'])) {
  echo "<h1>Select a theme to examine</h1><ul>";
  foreach (glob(dirname(__FILE__).'/*/') as $file) {
    $file = basename($file);
    echo "<li><a href=\"&#63;theme=$file\">$file</a>";
  }
  echo "</ul>";
} else {
  $theme = htmlentities($_GET['theme']);

  foreach (glob(dirname(__FILE__)."/$theme/*") as $image) {
    $image = basename($image);
    if ($image[0] == '.' || strstr($image,'~') || strstr($image, 'php'))
      continue;
    eregi('(.*)\.(.*)',$image,$regs);
    $images[$regs[1]][] = $regs[2];
  }
  $extras = $images;

  echo "<h1>$theme - required images</h1><table width=\"100%\">";

  foreach ($required_images as $image) {
    if ($i++%2 == 0)
      echo '<tr><td>&nbsp;<tr>';
    echo '<td align=left width="50%">';

    echo "<h3>$image</h3>";
    if ($images[$image]) {
      sort($images[$image]);

      foreach($images[$image] as $suffix)
        if( $suffix =='svg')
          echo "$suffix<img src=\"$theme/$image.$suffix\" align=middle width=\"50px\">&nbsp;&nbsp;&nbsp;";
        else
          echo "$suffix<img src=\"$theme/$image.$suffix\" align=middle>&nbsp;&nbsp;&nbsp;";
    } else {
      if ($image)
        echo "<font color=red>NO IMAGES</font>";
    }

    unset($extras[$image]);
  }

  echo "</table>";
  echo "<h2>$theme - additional images</h2><table width=\"100%\">";

  foreach ($extras as $image => $suffixes) {
    if ($j++%2 == 0)
      echo '<tr><td>&nbsp;<tr>';
    echo '<td align=left width="50%">';

    echo "<h3>$image</h3>";
    if ($images[$image]) {
      sort($images[$image]);

      foreach ($images[$image] as $suffix) {
        if( $suffix =='svg')
          echo "$suffix<img src=\"$theme/$image.$suffix\" align=middle width=\"50px\">&nbsp;&nbsp;&nbsp;";
        else
          echo "$suffix<img src=\"$theme/$image.$suffix\" align=middle>&nbsp;&nbsp;&nbsp;";
      }
    }
  }

  echo "</table>";
}
