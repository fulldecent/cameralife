<?php
/**
 * Displays the main admin page
 * Provides a menu to choose administrative options
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 Will Entriken
 */
$features=array('security', 'filestore');
require '../main.inc';
$cameralife->base_url = dirname($cameralife->base_url);

$numdel = $cameralife->Database->SelectOne('photos','COUNT(*)','status=1');
$numpri = $cameralife->Database->SelectOne('photos','COUNT(*)','status=2');
$numupl = $cameralife->Database->SelectOne('photos','COUNT(*)','status=3');
$numreg = $cameralife->Database->SelectOne('users','COUNT(*)','auth=1');
$numlog = $cameralife->Database->SelectOne('logs','COUNT(*)','id>'.($cameralife->GetPref('checkpointlogs')+0));
$numcomments = $cameralife->Database->SelectOne('comments','COUNT(*)','id>'.($cameralife->GetPref('checkpointcomments')+0));
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
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-52764-13']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
    </script>
  </head>

  <body>
    <div class="navbar navbar-inverse navbar-static-top">
      <div class="container">
        <span class="navbar-brand"><a href="../"><?= $cameralife->GetPref("sitename") ?></a> / Administration</span>
      </div>
    </div>
    <div class="container">
<?php
$latestVersion = trim(file_get_contents('https://raw.github.com/fulldecent/cameralife/master/VERSION'));
if ($cameralife->version == $latestVersion)
  echo "<p class=\"alert alert-success\">You are running Camera Life {$cameralife->version}, the latest version</p>\n";
else
  echo "<p class=\"alert alert-error\">A newer version of Camera Life, $latestVersion, is available. <a href=\"http://fulldecent.github.com/cameralife/\">Please visit the Camera Life homepage.</a></p>\n";

if ($cameralife->Security->authorize('admin_file')) {
?>
      <h1>Administration</h1>
      <div class="row">
        <div class="col-sm-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Logs</h3>
            </div>
            <div class="panel-body">
<?php
if ($numlog)
  echo "<p class=\"alert alert-warning\">There are $numlog logged actions since your last checkpoint</p>";
else
  echo "<p class=\"alert alert-info\">No changes have been made since your last checkpoint</p>";
?>
              <p><a class="btn btn-default" href="logs.php"><i class="icon-step-backward"></i> View and rollback site actions &raquo;</a></p>
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Comments</h3>
            </div>
            <div class="panel-body">
<?php
if ($numcomments)
  echo "<p class=\"alert alert-warning\">There are $numcomments comments since your last checkpoint</p>";
else
  echo "<p class=\"alert alert-info\">No changes have been made since your last checkpoint</p>";
?>
            <p><a class="btn btn-default" href="comments.php"><i class="icon-user"></i> View and censor site comments &raquo;</a></p>
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">File manager <small class="muted">and private photes</small></h3>
            </div>
            <div class="panel-body">
<?php
if ($numdel)
  echo "<p class=\"alert alert-danger\"><i class=\"icon-flag\"></i> $numdel photos have been flagged</p>";
if ($numupl)
  echo "<p class=\"alert alert-info\">$numupl photos have been uploaded but not reviewed</p>";
?>
            <p><a class="btn btn-default" href="files.php"><i class="icon-folder-open"></i> Manage files &raquo;</a></p>
            </div>
          </div>
        </div>
      </div>
<?php
}
if ($cameralife->Security->authorize('admin_customize')) {
?>
      <h1>Configuration</h1>

      <div class="row">
        <div class="col-sm-3">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Appearance</h3>
            </div>
            <div class="panel-body">
              <p><a class="btn btn-default" href="appearance.php"><i class="icon-star-empty"></i> Set theme and iconset &raquo;</a></p>
            </div>
          </div>
        </div>
        <div class="col-sm-3">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Security</h3>
            </div>
            <div class="panel-body">
<?php
if ($numreg)
  echo "<p class=\"alert alert-info\">$numreg users have registered but not been confirmed</p>\n";
?>
            <p><a class="btn btn-default" href="security.php"><i class="icon-lock"></i> Manage users &amp; security &raquo;</a></p>
            </div>
          </div>
        </div>
        <div class="col-sm-3">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">File storage</h3>
            </div>
            <div class="panel-body">
              <p>Your photos can be stored on your web server, a remote server, Amazon S3, etc.</p>
              <p><a class="btn btn-default" href="filestore.php"><i class="icon-folder-open"></i> Configure filestore &raquo;</a></p>
            </div>
          </div>
        </div>
        <div class="col-sm-3">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Feedback</h3>
            </div>
            <div class="panel-body">
              <p>How do you like Camera Life? Let us know.</p>
              <p>
                <a class="btn btn-default" href="https://github.com/fulldecent/cameralife/issues"><i class="icon-flag"></i> Report an issue &raquo;</a>
                <a class="btn btn-default" href="https://github.com/fulldecent/cameralife"><i class="icon-star"></i> Get project updates &raquo;</a>
                <a class="btn btn-default" href="http://www.facebook.com/sharer.php?u=http://fulldecent.github.com/cameralife/"><i class="icon-star"></i> Like on Facebook &raquo;</a>
              </p>
            </div>
          </div>
        </div>
      </div>
<?php
}
?>
    </div>
  </body>
</html>
