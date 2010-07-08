<?php
/**Modify the Look of Your Site
 *
 * You can set
 *<ul>
 *<li>Icons </li>
 *<li>Themes</li>
 *</ul>
 *
 *@link http://fdcl.sourceforge.net
 *@version 2.6.2
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 */
  $features=array('database','security','theme');
  require "../main.inc";
  require "admin.inc";
  $cameralife->base_url = dirname($cameralife->base_url);

  $cameralife->Security->authorize('admin_customize', 1); // Require

  if(!isset($_GET['page'])) $_GET['page'] = 'setup';

/**
 *Function to check if a directory exists or a directory is writable
 *
 * This function accepts one argument,a string value for file directory name
 *@param string $dir directory
 */
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

  $all_themes = array();
?>

<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <link rel="stylesheet" href="admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body>

<div id="header">
<h1>Site Administration &ndash; Appearance</h1>
<?php
  $home = $cameralife->GetIcon('small');
  echo '<a href="'.$home['href']."\"><img src=\"".$cameralife->IconURL('small-main')."\">".$home['name']."</a>\n";
?> |
<a href="index.php"><img src="<?= $cameralife->IconURL('small-admin')?>">Site Administration</a> | <a href="http://fdcl.sourceforge.net/index.php&#63;content=themes">Get more themes</a>
</div>

<form method="post" action="controller_prefs.php">
<input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'].'&#63;page='.$_GET['page'] ?>" />
<input type="hidden" name="module1" value="CameraLife" />
<input type="hidden" name="param1" value="theme" />
<input type="hidden" name="module2" value="CameraLife" />
<input type="hidden" name="param2" value="iconset" />
<table>
  <tr>
    <td>Choose a theme engine
    <td>
      <select name="value1">
      <?php
        $themes = glob($cameralife->base_dir."/modules/theme/*");
        foreach($themes as $theme)
        {
          if ($file[0] == '.')
            continue;
          if (!is_dir($theme))
            continue;
          if (!is_file($theme."/theme-info.php"))
            continue;

          include($theme."/theme-info.php");

          if ($cameralife->GetPref('theme') == basename($theme))
            echo "<option selected value=\"".basename($theme)."\">\n";
          else
            echo "<option value=\"".basename($theme)."\">\n";

          echo "<b>$theme_name</b> - <i>version $theme_version by $theme_author</i>";
          echo "</option>\n";
          flush();
        }

      ?>
      </select>
    <td><input type="submit" value="Choose">

  <tr>
    <td>Choose an iconset
    <td>
      <select name="value2">
      <?php
        $themes = glob($cameralife->base_dir."/modules/iconset/*");
        foreach($themes as $theme)
        {
          if ($file[0] == '.')
            continue;
          if (!is_dir($theme))
            continue;
          if (!is_file($theme."/iconset-info.php"))
            continue;

          include($theme."/iconset-info.php");

          if ($cameralife->GetPref('iconset') == basename($theme))
            echo "<option selected value=\"".basename($theme)."\">\n";
          else
            echo "<option value=\"".basename($theme)."\">\n";

          echo "<b>$iconset_name</b> - <i>version $iconset_version by $iconset_author</i>";
          echo "</option>\n";
          flush();
        }
      ?>
      </select>
    <td><input type="submit" value="Choose">

</table>
</form>

<form method="post" action="controller_prefs.php">
<input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'].'&#63;page='.$_GET['page'] ?>" />
<h2>Site Parameters</h2>
<table>
  <tr>
    <td>Site name
    <td>
      <input type="hidden" name="module1" value="CameraLife" />
      <input type="hidden" name="param1" value="sitename" />
      <input type=text name="value1" size=30 value="<?= $cameralife->GetPref('sitename') ?>">
  <tr>
    <td>Site abbreviation (used to refer to the main page)
    <td>
      <input type="hidden" name="module2" value="CameraLife" />
      <input type="hidden" name="param2" value="siteabbr" />
      <input type=text name="value2" size=30 value="<?= $cameralife->GetPref('siteabbr') ?>">
  <tr>
    <td>Owner E-mail address (shown if something goes wrong)
    <td>
      <input type="hidden" name="module3" value="CameraLife" />
      <input type="hidden" name="param3" value="owner_email" />
      <input type=text name="value3" size=30 value="<?= $cameralife->GetPref('owner_email') ?>">
  <tr>
    <td>Use pretty URL's (requires mod rewrite, and please edit .htaccess)
    <td>
      <input type="hidden" name="module4" value="CameraLife" />
      <input type="hidden" name="param4" value="rewrite" />
      <select name="value4">
        <option <?= $cameralife->GetPref('rewrite') == 'no' ? 'selected="selected"':'' ?>>no</option>
        <option <?= $cameralife->GetPref('rewrite') == 'yes' ? 'selected="selected"':'' ?>>yes</option>
      </select>
  <tr>
    <td>Use the iPhone theme for iPhones and iPod touches?
    <td>
      <input type="hidden" name="module5" value="CameraLife" />
      <input type="hidden" name="param5" value="iphone" />
      <select name="value5">
        <option <?= $cameralife->GetPref('iphone') == 'no' ? 'selected="selected"':'' ?>>no</option>
        <option <?= $cameralife->GetPref('iphone') == 'yes' ? 'selected="selected"':'' ?>>yes</option>
      </select>
  <tr>
    <td>Use photo metadata to autorotate photos? (update existing photos with hacks/exif.php)
    <td>
      <input type="hidden" name="module5" value="CameraLife" />
      <input type="hidden" name="param5" value="autorotate" />
      <select name="value5">
        <option <?= $cameralife->GetPref('autorotate') == 'no' ? 'selected="selected"':'' ?>>no</option>
        <option <?= $cameralife->GetPref('autorotate') == 'yes' ? 'selected="selected"':'' ?>>yes</option>
      </select>
  <tr>
    <td>Size for thumbnails (in pixels)
    <td>
      <input type="hidden" name="module6" value="CameraLife" />
      <input type="hidden" name="param6" value="thumbsize" />
      <input type=text name="value6" size=10 value="<?= $cameralife->GetPref('thumbsize') ?>">
  <tr>
    <td>Size for scaled images (in pixels)
    <td>
      <input type="hidden" name="module7" value="CameraLife" />
      <input type="hidden" name="param7" value="scaledsize" />
      <input type=text name="value7" size=10 value="<?= $cameralife->GetPref('scaledsize') ?>">
  <tr>
    <td>Additional sizes for the user to view photos. Comma separated. You can also leave this blank.
    <td>
      <input type="hidden" name="module8" value="CameraLife" />
      <input type="hidden" name="param8" value="optionsizes" />
      <input type=text name="value8" size=30 value="<?= join(',',preg_split('/[, ]+/',$cameralife->GetPref('optionsizes'))) ?>">
  <tr>
    <td><td><input type="submit" value="Save changes" />
</table>
</form>

<?php renderPrefsAsHTML($cameralife->Theme) ?>

</body>
</html>

