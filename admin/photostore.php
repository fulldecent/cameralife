<?php
  # Sets the options for your site...
 /**Enables you set options for your site
  *@link http://fdcl.sourceforge.net
  *@version 2.6.3b4
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
*/
/**
*/
  $features=array('database','security','theme', 'photostore');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);

  $cameralife->Security->authorize('admin_file', 1); // Require

  $_GET['page'] or $_GET['page'] = 'setup';

  function check_dir($dir)
  {
    global $cameralife;

    if ($dir[0] != '/')
      $dir = $cameralife->base_dir."/$dir/";
    if (!is_dir($dir) )
      echo "<p class=\"alert\">WARNING: $dir is not a directory!</p>";
	elseif (!is_writable($dir))
      echo "<p class=\"alert\">WARNING: $dir is not writable!</p>";
  }
?>

<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <link rel="stylesheet" href="admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
  <script language="javascript">
  <!--
    function toggleElement(a,b)
    {
      document.getElementById(a).style.display = 'block'
      document.getElementById(b).style.display = 'none'
      return false;
    }
  -->
  </script>
</head>
<body>

<div id="header">
<h1>Site Administration &ndash; Photo Storage</h1>
<?php
  $home = $cameralife->GetIcon();
  echo '<a href="'.$home['href']."\"><img src=\"".$cameralife->IconURL('small-main')."\">".$home['name']."</a>\n";
?> |
<a href="index.php"><img src="<?= $cameralife->IconURL('small-admin')?>">Site Administration</a>
</div>

<form method="post" action="controller_prefs.php">
<input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'].'&#63;page='.$_GET['page'] ?>" />
<input type="hidden" name="module1" value="CameraLife" />
<input type="hidden" name="param1" value="photostore" />
<p id="warning">You can keep your photos on this computer or save them remotely on Amazon S3, Flickr, or a server your control. To change your photostore, <a href="#" onclick="toggleElement('info','warning')">click here</a>.</p>

<div id="info" style="display: none">
  <h3>If you have no photos:</h3>
  <p>Just change the drop-down below and configure below.</p>

  <h3>If you want to keep existing photos:</h3>
  <p>If you load any other pages during this process, the consequences could be dire.</p>
<ol>
  <li>Edit main.inc, uncomment/edit the first couple lines, to keep other people away from your site</li>
  <li><a href="../hacks/backup.php" target="_new">Backup your photostore</a></li>
  <li>Change the photostore here</li>
  <li><a href="../hacks/restore.php" target="_new">Restore your photostore</a></li>
  <li>Unedit main.inc</li>
</ol>

<table>
  <tr>
    <td>Choose a photo storage engine
    <td>
      <select name="value1">
      <?php
        $themes = glob($cameralife->base_dir."/modules/photostore/*");
        foreach($themes as $theme)
        {
          if (!is_dir($theme))
            continue;

          if ($cameralife->GetPref('photostore') == basename($theme))
            echo "<option selected value=\"".basename($theme)."\">\n";
          else
            echo "<option value=\"".basename($theme)."\">\n";

          echo basename($theme);
          echo "</option>\n";
          flush();
        }

      ?>
      </select>
    <td><input type="submit" value="Choose">

</table>
</form>
</div>

<form method="post" action="controller_prefs.php">
<input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'] ?>" />
<h2>Settings for <?= $cameralife->GetPref('photostore') ?></h2>
<p><?= $cameralife->PhotoStore->about ?></p>
<table>
<?php
  $prefnum=0;
  foreach ($cameralife->PhotoStore->preferences as $pref)
  {
    $prefnum++;
    echo "  <tr><td>".$pref['desc']."\n";
    echo "    <td>\n";
    echo "      <input type=\"hidden\" name=\"module$prefnum\" value=\"".get_class($cameralife->PhotoStore)."\" />\n";
    echo "      <input type=\"hidden\" name=\"param$prefnum\" value=\"".$pref['name']."\" />\n";

    $value = $cameralife->PhotoStore->GetPref($pref['name']);

    if ($pref['type'] == 'number' || $pref['type'] == 'string' || $pref['type'] == 'directory')
    {
      echo "      <input type=\"text\" name=\"value$prefnum\" value=\"$value\" />\n";
    }
    elseif (is_array($pref['type'])) // enumeration
    {
      echo "      <select name=\"value$prefnum\" />\n";
      foreach($pref['type'] as $index=>$desc)
      {
        if ($index == $value)
          echo "        <option selected value=\"$index\">$desc</option>\n";
        else
          echo "        <option value=\"$index\">$desc</option>\n";
      }
      echo "      </select />\n";
    }
    elseif ($pref['type'] == 'yesno')
    {
      echo "      <select name=\"value$prefnum\" />\n";
      foreach(array('1'=>'Yes', '0'=>'No') as $index=>$desc)
      {
        if ($index == $value)
          echo "        <option selected value=\"$index\">$desc</option>\n";
        else
          echo "        <option value=\"$index\">$desc</option>\n";
      }
      echo "      </select />\n";
    }
  }
?>
  <tr><td><td><input type="submit" value="Save changes" />
</table>
</form>


</body>
</html>

