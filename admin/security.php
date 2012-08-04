<?php
/**Modify user security
 *
 * You can set
 *<ul>
 *<li>Icons </li>
 *<li>Themes</li>
 *</ul>
 *
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
?>

<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <link rel="stylesheet" href="admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body>

<div id="header">
<h1>Site Administration &ndash; Security Manager</h1>
<?php
  $home = $cameralife->GetIcon('small');
  echo '<a href="'.$home['href']."\"><img src=\"".$cameralife->IconURL('small-main')."\">".$home['name']."</a>\n";
?> |
<a href="index.php"><img src="<?= $cameralife->IconURL('small-admin')?>">Site Administration</a> 
</div>

<form method="post" action="controller_prefs.php">
<input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'].'&#63;page='.$_GET['page'] ?>" />
<input type="hidden" name="module1" value="CameraLife" />
<input type="hidden" name="param1" value="security" />
<input type="hidden" name="module2" value="CameraLife" />
<input type="hidden" name="param2" value="iconset" />
<h2>Site Security Module</h2>
<table>
  <tr>
    <td>Choose a security implementation
    <td>
      <select name="value1">
      <?php
        $feature = 'security';
        foreach ($cameralife->GetModules($feature) as $module)
        {
          include $cameralife->base_dir."/modules/$feature/$module/module-info.php";

          $selected = $cameralife->GetPref($feature) == basename($module) ? 'selected' : '';
          echo "<option $selected value=\"$module\">";
          echo "<b>$module_name</b> - <i>version $module_version by $module_author</i>";
          echo "</option>\n";
        }
      ?>
      </select>
    <td><input type="submit" value="Choose">
  <?php
    if($url = $cameralife->Security->AdministerURL())
    {
      echo "<tr><td colspan=3>You can <a href=\"$url\">access user adminstration settings</a> for this module.";
    }
  ?>
</table>
</form>

<form>
<h2>Your Access</h2>
<table>
  <tr>
    <td>
<?php
  $name = $cameralife->Security->GetName();
  if ($name)
    echo "You are logged in as <b>$name</b>";
  else
    echo "You are not logged in";

  echo "<h3>Permissions</h1><ul>";
  $perms = array("photo_rename", "photo_delete", "photo_modify", "admin_albums", "photo_upload", "admin_file", "admin_theme", "admin_customize");
  foreach ($perms as $perm)
  {
    $access = $cameralife->Security->Authorize($perm) ? "Yes" : "No";
    echo "<li>$perm -- <b>$access</b></li>\n";
  }
  echo "</ul>"; 

?>
</table>
</form>

</body>
</html>

