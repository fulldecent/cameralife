<?php
/*
 * Modify the look of your site, configure Themes and Icons
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 Will Entriken
 * @access public
 */
$features=array('database','security', 'photostore', 'theme');
require '../main.inc';
$cameralife->base_url = dirname($cameralife->base_url);
$cameralife->Security->authorize('admin_customize', 1); // Require
if(!isset($_GET['page'])) $_GET['page'] = 'setup';
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
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    <link href="../bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>
  </head>

  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <span class="brand"><a href="../"><?= $cameralife->GetPref("sitename") ?></a> / <a href="index.php">Administration</a> / Appearance</span>
        </div>
      </div>
    </div>
    <div class="container">
      <h2>Modules</h2>

      <form class="form-horizontal well" method="post" action="controller_prefs.php">
        <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'].'&#63;page='.$_GET['page'] ?>" />
        <input type="hidden" name="module1" value="CameraLife" />
        <input type="hidden" name="param1" value="theme" />
        <input type="hidden" name="module2" value="CameraLife" />
        <input type="hidden" name="param2" value="iconset" />

        <div class="control-group">
          <label class="control-label" for="inputTheme">Theme engine</label>
          <div class="controls">
            <select name="value1" id="inputTheme" class="input-xxlarge">
<?php
$feature = 'theme';
foreach ($cameralife->GetModules($feature) as $module) {
  include $cameralife->base_dir."/modules/$feature/$module/module-info.php";

  $selected = $cameralife->GetPref($feature) == basename($module) ? 'selected' : '';
  echo "<option $selected value=\"$module\">";
  echo "<b>$module_name</b> - <i>version $module_version by $module_author</i>";
  echo "</option>\n";
}
?>
            </select>
            <input type="submit" value="Choose" class="btn">
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="inputIconset">Iconset</label>
          <div class="controls">
            <select name="value2" id="inputIconset" class="input-xxlarge">
<?php
$feature = 'iconset';
foreach ($cameralife->GetModules($feature) as $module) {
  include $cameralife->base_dir."/modules/$feature/$module/module-info.php";

  $selected = $cameralife->GetPref($feature) == basename($module) ? 'selected' : '';
  echo "<option $selected value=\"$module\">";
  echo "<b>$module_name</b> - <i>version $module_version by $module_author</i>";
  echo "</option>\n";
}
?>
            </select>
            <input type="submit" value="Choose" class="btn">
          </div>
        </div>
      </form>

      <h2>Site Parameters</h2>

      <form method="post" action="controller_prefs.php" class="form-horizontal">
        <div class="control-group">
          <label class="control-label" for="sitename">Site name</label>
          <div class="controls">
            <input type="hidden" name="module1" value="CameraLife" />
            <input type="hidden" name="param1" value="sitename" />
            <input type="text" id="sitename" name="value1" size=30 value="<?= $cameralife->GetPref('sitename') ?>">
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="siteabbr">Site abbreviation</label>
          <div class="controls">
            <input type="hidden" name="module2" value="CameraLife" />
            <input type="hidden" name="param2" value="siteabbr" />
            <input type="text" id="siteabbr" name="value2" size=30 value="<?= $cameralife->GetPref('siteabbr') ?>">
            <span class="help-inline">used to refer to the main page</span>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="owner_email">Owner E-mail address</label>
          <div class="controls">
            <input type="hidden" name="module3" value="CameraLife" />
            <input type="hidden" name="param3" value="owner_email" />
            <input type="text" id="owner_email" name="value3" size=30 value="<?= $cameralife->GetPref('owner_email') ?>">
            <span class="help-inline">shown if something goes wrong</span>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="rewrite">Use pretty URL's</label>
          <div class="controls">
            <input type="hidden" name="module4" value="CameraLife" />
            <input type="hidden" name="param4" value="rewrite" />
            <select name="value4" id="rewrite">
              <option <?= $cameralife->GetPref('rewrite') == 'no' ? 'selected="selected"':'' ?>>no</option>
              <option <?= $cameralife->GetPref('rewrite') == 'yes' ? 'selected="selected"':'' ?>>yes</option>
            </select>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="iphone">Use the iPhone theme</label>
          <div class="controls">
            <input type="hidden" name="module5" value="CameraLife" />
            <input type="hidden" name="param5" value="iphone" />
            <select name="value5" id="iphone">
              <option <?= $cameralife->GetPref('iphone') == 'no' ? 'selected="selected"':'' ?>>no</option>
              <option <?= $cameralife->GetPref('iphone') == 'yes' ? 'selected="selected"':'' ?>>yes</option>
            </select>
            <span class="help-inline">shown on iPhones and iPod touches</span>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="autorotate">Autorotate photos</label>
          <div class="controls">
            <input type="hidden" name="module6" value="CameraLife" />
            <input type="hidden" name="param6" value="autorotate" />
            <select name="value6" id="autorotate">
              <option <?= $cameralife->GetPref('autorotate') == 'no' ? 'selected="selected"':'' ?>>no</option>
              <option <?= $cameralife->GetPref('autorotate') == 'yes' ? 'selected="selected"':'' ?>>yes</option>
            </select>
            <span class="help-inline">Update existing photos with hacks/exif.php</span>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="thumbsize">Size for thumbnails</label>
          <div class="controls">
            <input type="hidden" name="module7" value="CameraLife" />
            <input type="hidden" name="param7" value="thumbsize" />
            <input type="number" id="thumbsize" name="value7" size=10 value="<?= $cameralife->GetPref('thumbsize') ?>">
            <span class="help-inline">in pixels</span>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="scaledsize">Size for preview images</label>
          <div class="controls">
            <input type="hidden" name="module8" value="CameraLife" />
            <input type="hidden" name="param8" value="scaledsize" />
            <input type="number" id="scaledsize" name="value8" size=30 value="<?= $cameralife->GetPref('scaledsize') ?>">
            <span class="help-inline">in pixels</span>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="optionsizes">Other sizes users can see</label>
          <div class="controls">
            <input type="hidden" name="module9" value="CameraLife" />
            <input type="hidden" name="param9" value="optionsizes" />
            <input type="text" id="optionsizes" name="value9" size=30 value="<?= join(',',preg_split('/[, ]+/',$cameralife->GetPref('optionsizes'))) ?>">
            <span class="help-inline">comma separated (you can also leave this blank)</span>
          </div>
        </div>
        <div class="control-group">
          <div class="controls">
            <input type="submit" value="Save changes" class="btn btn-primary"/>
          </div>
        </div>
      </form>

<?php renderPrefsAsHTML($cameralife->Theme) ?>
  </body>
</html>
