<?php
/*
 * Set filestore module
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 Will Entriken
 * @access public
 */
$features=array('security', 'filestore', 'theme');
require '../main.inc';
$cameralife->baseURL = dirname($cameralife->baseURL);
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
        <a onclick="$('#chgps').show();$('#chgpshead').hide()" class="btn btn-default" id="chgpshead">To change your filestore, click here</a>
        <div style="display:none" id="chgps">
          <h3>If you have no photos:</h3>
          <p>Just change the drop-down and configure below</p>

          <h3>If you want to keep existing photos:</h3>
          <p class="text-error">Warning: backup your photos and database before you try these instructions. If you load any other pages during this process, the consequences could be dire.</p>

          <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'].'&#63;page='.$_GET['page'] ?>" />
          <input type="hidden" name="module1" value="CameraLife" />
          <input type="hidden" name="param1" value="theme" />
          <input type="hidden" name="module2" value="CameraLife" />
          <input type="hidden" name="param2" value="iconset" />

          <div class="control-group">
            <label class="control-label" for="inputTheme">Filestore</label>
            <div class="controls">
              <select name="value1" id="inputTheme" class="form-control">
<?php
$feature = 'filestore';
foreach ($cameralife->GetModules($feature) as $module) {
  echo "<option $selected value=\"$module\">";
  echo "<b>$module</b>";
  echo "</option>\n";
}
?>
              </select>
              <input type="submit" value="Choose" class="btn btn-default">
            </div>
          </div>
        </div>
      </form>

      <?php renderPrefsAsHTML($cameralife->FileStore); ?>
    </div>
  </body>
</html>
