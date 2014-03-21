<?php
/*
 * Modify user security
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 Will Entriken
 * @access public
 */
$features=array('security');
require '../main.inc';
$cameralife->base_url = dirname($cameralife->base_url);
$cameralife->Security->authorize('admin_customize', 1); // Require
require 'admin.inc';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Camera Life - Administration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
  </head>

  <body>
    <div class="navbar navbar-inverse navbar-static-top">
      <div class="container">
        <span class="navbar-brand"><a href="../"><?= $cameralife->GetPref("sitename") ?></a> / Administration</span>
      </div>
    </div>
    <div class="container">
      <h2>Module</h2>

      <form class="form-horizontal well" method="post" action="controller_prefs.php">
        <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'].'&#63;page='.$_GET['page'] ?>" />
        <input type="hidden" name="module1" value="CameraLife" />
        <input type="hidden" name="param1" value="security" />

        <div class="control-group">
          <label class="control-label" for="inputTheme">Security module</label>
          <div class="controls">
            <select name="value1" id="inputTheme" class="input-xxlarge">
<?php
$feature = 'security';
foreach ($cameralife->GetModules($feature) as $module) {
  $info = json_decode(file_get_contents($cameralife->base_dir."/modules/$feature/$module/module-info.json"));
  $selected = $cameralife->GetPref($feature) == basename($module) ? 'selected' : '';
  echo "<option $selected value=\"{$module}\">";
  echo "<b>{$info->name}</b> - <i>version {$info->version} by {$info->author}</i>";
  echo "</option>\n";
}
?>
            </select>
            <input type="submit" value="Choose" class="btn btn-default">
          </div>
        </div>
<?php
if ($url = $cameralife->Security->AdministerURL()) {
  echo "<p>You can <a href=\"$url\">access administration settings</a> for this module.</p>";
}
?>
      </form>
      <h2>Your access (for user <?= $cameralife->Security->GetName() ?>)</h2>
      <table class="table table-striped">
<?php
  $perms = array("photo_rename", "photo_delete", "photo_modify", "admin_albums", "photo_upload", "admin_file", "admin_theme", "admin_customize");
  foreach ($perms as $perm) {
    $access = $cameralife->Security->Authorize($perm) ? "Yes" : "No";
    echo "<tr><td>$perm<td>$access\n";
  }
?>
      </table>
    </div>
  </body>
</html>
