<?php
/*
 * Set photostore module
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 Will Entriken
 * @access public
 */
$features=array('database','security', 'photostore', 'theme');
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
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>
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
        <a onclick="$('#chgps').show();$('#chgpshead').hide()" class="btn btn-default" id="chgpshead">To change your photostore, click here</a>
        <div style="display:none" id="chgps">
          <h3>If you have no photos:</h3>
          <p>Just change the drop-down and configure below</p>

          <h3>If you want to keep existing photos:</h3>
          <p class="text-error">Warning: backup your photos and database before you try these instructions. If you load any other pages during this process, the consequences could be dire.</p>
          <ol>
            <li>Edit main.inc, uncomment/edit the first couple lines, to keep other people away from your site</li>
            <li><a href="../hacks/backup.php" target="_new">Backup your photostore</a></li>
            <li>Change the photostore here</li>
            <li><a href="../hacks/restore.php" target="_new">Restore your photostore</a></li>
            <li>Unedit main.inc</li>
          </ol>

          <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'].'&#63;page='.$_GET['page'] ?>" />
          <input type="hidden" name="module1" value="CameraLife" />
          <input type="hidden" name="param1" value="theme" />
          <input type="hidden" name="module2" value="CameraLife" />
          <input type="hidden" name="param2" value="iconset" />

          <div class="control-group">
            <label class="control-label" for="inputTheme">Photostore</label>
            <div class="controls">
              <select name="value1" id="inputTheme" class="input-xxlarge">
<?php
$feature = 'photostore';
foreach ($cameralife->GetModules($feature) as $module) {
  include $cameralife->base_dir."/modules/$feature/$module/module-info.php";
  $selected = $cameralife->GetPref($feature) == basename($module) ? 'selected' : '';
  echo "<option $selected value=\"$module\">";
  echo "<b>$module_name</b> - <i>version $module_version by $module_author</i>";
  echo "</option>\n";
}
?>
              </select>
              <input type="submit" value="Choose" class="btn btn-default">
            </div>
          </div>
        </div>
      </form>

      <?php renderPrefsAsHTML($cameralife->PhotoStore); ?>
    </div>
  </body>
</html>
