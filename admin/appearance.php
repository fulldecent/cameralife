<?php
/*
 * Modify the look of your site, configure Themes and Icons
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 Will Entriken
 * @access public
 */
$features=array('security', 'theme');
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
      <h2>Modules</h2>

      <form class="form-horizontal well" method="post" action="controller_prefs.php">
        <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'].'&#63;page='.$_GET['page'] ?>" />
        <input type="hidden" name="module1" value="CameraLife" />
        <input type="hidden" name="param1" value="theme" />
        <input type="hidden" name="module2" value="CameraLife" />
        <input type="hidden" name="param2" value="iconset" />

        <div class="form-group">
          <label class="col-lg-2 control-label" for="inputTheme">Theme engine</label>
          <div class="col-lg-10">
            <select name="value1" id="inputTheme" class="input-xxlarge">
<?php
$feature = 'theme';
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
        <div class="form-group">
          <label class="col-lg-2 control-label" for="inputIconset">Iconset</label>
          <div class="col-lg-10">
            <select name="value2" id="inputIconset" class="input-xxlarge">
<?php
$feature = 'iconset';
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
      </form>

      <h2>Site Parameters</h2>

      <form method="post" action="controller_prefs.php" class="form-horizontal">
        <div class="form-group">
          <label class="col-lg-2 control-label" for="sitename">Site name</label>
          <div class="col-lg-10 controls">
            <input type="hidden" name="module1" value="CameraLife" />
            <input type="hidden" name="param1" value="sitename" />
            <input type="text" id="sitename" name="value1" size=30 value="<?= $cameralife->GetPref('sitename') ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-2 control-label" for="siteabbr">Site abbreviation</label>
          <div class="col-lg-10 controls">
            <input type="hidden" name="module2" value="CameraLife" />
            <input type="hidden" name="param2" value="siteabbr" />
            <input type="text" id="siteabbr" name="value2" size=30 value="<?= $cameralife->GetPref('siteabbr') ?>">
            <span class="help-inline">used to refer to the main page</span>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-2 control-label" for="owner_email">Owner E-mail address</label>
          <div class="col-lg-10 controls">
            <input type="hidden" name="module3" value="CameraLife" />
            <input type="hidden" name="param3" value="owner_email" />
            <input type="text" id="owner_email" name="value3" size=30 value="<?= $cameralife->GetPref('owner_email') ?>">
            <span class="help-inline">shown if something goes wrong</span>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-2 control-label" for="rewrite">Use pretty URL's</label>
          <div class="col-lg-10 controls">
            <input type="hidden" name="module4" value="CameraLife" />
            <input type="hidden" name="param4" value="rewrite" />
            <select name="value4" id="rewrite">
              <option <?= $cameralife->GetPref('rewrite') == 'no' ? 'selected="selected"':'' ?>>no</option>
              <option <?= $cameralife->GetPref('rewrite') == 'yes' ? 'selected="selected"':'' ?>>yes</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-2 control-label" for="autorotate">Autorotate photos</label>
          <div class="col-lg-10 controls">
            <input type="hidden" name="module6" value="CameraLife" />
            <input type="hidden" name="param6" value="autorotate" />
            <select name="value6" id="autorotate">
              <option <?= $cameralife->GetPref('autorotate') == 'no' ? 'selected="selected"':'' ?>>no</option>
              <option <?= $cameralife->GetPref('autorotate') == 'yes' ? 'selected="selected"':'' ?>>yes</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-2 control-label" for="thumbsize">Size for thumbnails</label>
          <div class="col-lg-10 controls">
            <input type="hidden" name="module7" value="CameraLife" />
            <input type="hidden" name="param7" value="thumbsize" />
            <input type="number" id="thumbsize" name="value7" size=10 value="<?= $cameralife->GetPref('thumbsize') ?>">
            <span class="help-inline">in pixels</span>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-2 control-label" for="scaledsize">Size for preview images</label>
          <div class="col-lg-10 controls">
            <input type="hidden" name="module8" value="CameraLife" />
            <input type="hidden" name="param8" value="scaledsize" />
            <input type="number" id="scaledsize" name="value8" size=30 value="<?= $cameralife->GetPref('scaledsize') ?>">
            <span class="help-inline">in pixels</span>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-2 control-label" for="optionsizes">Other sizes users can see</label>
          <div class="col-lg-10 controls">
            <input type="hidden" name="module9" value="CameraLife" />
            <input type="hidden" name="param9" value="optionsizes" />
            <input type="text" id="optionsizes" name="value9" size=30 value="<?= join(',',preg_split('/[, ]+/',$cameralife->GetPref('optionsizes'))) ?>">
            <span class="help-inline">comma separated (you can also leave this blank)</span>
          </div>
        </div>
        <div class="form-group">
          <div class="col-lg-10 controls">
            <input type="submit" value="Save changes" class="btn btn-primary"/>
          </div>
        </div>
      </form>

<?php renderPrefsAsHTML($cameralife->Theme) ?>
  </body>
</html>
