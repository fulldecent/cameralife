<?php
/*
 * Modify the look of your site, configure Themes and Icons
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 William Entriken
 * @access public
 */
$features = array('security', 'theme');
require '../main.inc';
$cameralife = CameraLife::cameraLifeWithFeatures($features);
$cameralife->baseURL = dirname($cameralife->baseURL);
$cameralife->security->authorize('admin_customize', 1); // Require
if (!isset($_GET['page'])) {
    $_GET['page'] = 'setup';
}
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
        <span class="navbar-brand"><a href="../"><?= $cameralife->getPref("sitename") ?></a> / Administration</span>
    </div>
</div>
<div class="container">
    <h2>Modules</h2>

    <form class="form-horizontal well" method="post" action="controller_prefs.php">
        <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'] . '&#63;page=' . htmlspecialchars($_GET['page']) ?>"/>
        <div class="form-group form-inline">
            <label class="col-md-2 control-label" for="inputTheme">Theme engine</label>
            <div class="col-md-10">
                <select name="CameraLife|theme" id="inputTheme" class="form-control">
                    <?php
                    $feature = 'theme';
                    foreach ($cameralife->getModules($feature) as $module) {
                        $info = json_decode(
                            file_get_contents($cameralife->baseDir . "/modules/$feature/$module/module-info.json")
                        );
                        $selected = $cameralife->getPref($feature) == basename($module) ? 'selected' : '';
                        echo "<option $selected value=\"{$module}\">";
                        echo "<b>{$info->name}</b> - <i>version {$info->version} by {$info->author}</i>";
                        echo "</option>\n";
                    }
                    ?>
                </select>
                <input type="submit" value="Choose" class="btn btn-default">
            </div>
        </div>
        <div class="form-group form-inline">
            <label class="col-md-2 control-label" for="inputIconset">Iconset</label>
            <div class="col-md-10">
                <select name="CameraLife|iconset" id="inputIconset" class="form-control">
                    <?php
                    $feature = 'iconset';
                    foreach ($cameralife->getModules($feature) as $module) {
                        $info = json_decode(
                            file_get_contents($cameralife->baseDir . "/modules/$feature/$module/module-info.json")
                        );
                        $selected = $cameralife->getPref($feature) == basename($module) ? 'selected' : '';
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
        <div class="form-group form-inline">
            <label class="col-md-2 control-label" for="sitename">Site name</label>
            <div class="col-md-10 form-inline">
                <input type="text" id="sitename" name="CameraLife|sitename" size=30 value="<?= $cameralife->getPref('sitename') ?>" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label" for="siteabbr">Site abbreviation</label>
            <div class="col-md-10 form-inline">
                <input type="text" id="siteabbr" name="CameraLife|siteabbr" size=30 value="<?= $cameralife->getPref('siteabbr') ?>" class="form-control" style="width:auto; display:inline-block">
                <span class="text-muted">refers to the main page</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label" for="owner_email">Owner E-mail address</label>
            <div class="col-md-10 form-inline">
                <input type="text" id="owner_email" name="CameraLife|owner_email" size=30
                       value="<?= $cameralife->getPref('owner_email') ?>" class="form-control">
                <span class="text-muted">shown if something goes wrong</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label" for="rewrite">Use pretty URL's</label>

            <div class="col-md-10 form-inline">
                <select name="CameraLife|rewrite" id="rewrite" class="form-control">
                    <option <?= $cameralife->getPref('rewrite') == 'no' ? 'selected="selected"' : '' ?>>no</option>
                    <option <?= $cameralife->getPref('rewrite') == 'yes' ? 'selected="selected"' : '' ?>>yes</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label" for="autorotate">Autorotate photos</label>

            <div class="col-md-10 form-inline">
                <select name="CameraLife|autorotate" id="autorotate" class="form-control">
                    <option <?= $cameralife->getPref('autorotate') == 'no' ? 'selected="selected"' : '' ?>>no</option>
                    <option <?= $cameralife->getPref('autorotate') == 'yes' ? 'selected="selected"' : '' ?>>yes</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label" for="thumbsize">Size for thumbnails</label>
            <div class="col-md-10 form-inline">
                <input type="number" id="thumbsize" name="CameraLife|thumbsize" size=10
                       value="<?= $cameralife->getPref('thumbsize') ?>" class="form-control">
                <span class="text-muted">in pixels</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label" for="scaledsize">Size for preview images</label>

            <div class="col-md-10 form-inline">
                <input type="number" id="scaledsize" name="CameraLife|scaledsize" size=10
                       value="<?= $cameralife->getPref('scaledsize') ?>" class="form-control">
                <span class="text-muted">in pixels</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label" for="optionsizes">Other available sizes</label>
            <div class="col-md-10 form-inline">
                <input type="text" id="optionsizes" name="CameraLife|optionsizes" size=30
                       value="<?= join(',', preg_split('/[, ]+/', $cameralife->getPref('optionsizes'))) ?>" class="form-control">
                <span class="text-muted">comma separated (you can also leave this blank)</span>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-10 form-inline">
                <input type="submit" value="Save changes" class="btn btn-primary"/>
            </div>
        </div>
    </form>

    <?php renderPrefsAsHTML($cameralife->theme) ?>
</body>
</html>
