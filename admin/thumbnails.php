<?php
/*
 * Regenerates thumbnail caches
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 Will Entriken
 * @access public
 */
$features=array('database','security', 'photostore', 'imageprocessing');
@ini_set('max_execution_time',9000);
require '../main.inc';
$cameralife->Security->authorize('admin_customize', 1); // Require
$cameralife->base_url = dirname($cameralife->base_url);
chdir ($cameralife->base_dir);
require 'admin.inc';
$cameralife->Security->authorize('admin_file', 1); // Require
$lastdone = (int) $_GET['lastdone']
  or $lastdone = -1;
$starttime = (int) $_GET['starttime']
  or $starttime = time();
$numdone = (int) $_GET['numdone']
  or $numdone = 0;
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
          <span class="brand"><a href="../"><?= $cameralife->GetPref("sitename") ?></a> / <a href="index.php">Administration</a> / Thumbnails</span>
        </div>
      </div>
    </div>
    <div class="container">
      <h2>Update thumbnails</h2>
      <p>We are now caching thumbnails. This avoids a delay when a photo is viewed for the first time.</p>

<?php
  $total = $cameralife->Database->SelectOne('photos', 'count(*)');
  $done = $cameralife->Database->SelectOne('photos', 'count(*)', "id <= $lastdone");
  $todo = $cameralife->Database->SelectOne('photos', 'count(*)', "id > $lastdone");
  $timeleft = ceil((time()-$starttime) * $todo / ($numdone + $done/500 + 1) / 60);

  echo "<p>Progress: $done of $total done";
  if ($done != $total)
    echo " (about $timeleft minutes left)";
  echo "</p>\n";
  echo '<div class="progress progress-striped active">';
  echo '<div class="bar" style="width: '.($done/$total*100).'%;"></div>';
  echo '</div>';

  $next1000 = $cameralife->Database->Select('photos', 'id', "id > $lastdone", 'ORDER BY id LIMIT 500');
  $fixed = 0;
  flush();
  while (($next = $next1000->FetchAssoc()) && ($fixed < 10)) {
    $curphoto = new Photo($next['id']);
    if ($cameralife->PhotoStore->CheckThumbnails($curphoto)) {
      echo "<div>Updated #".$next['id']."</div>\n";
      flush();
      $fixed++;
    }
    $curphoto->Destroy();
    $lastdone = $next['id'];
  }

  $numdone += $fixed;
  if ($todo > 0) {
    echo "<script language='javascript'>window.setTimeout('window.location=\"".$_SERVER['PHP_SELF']."?lastdone=$lastdone&starttime=$starttime&numdone=$numdone\"',600)</script>\n" ;
    echo "<p><a href=\"thumbnails.php?lastdone=$lastdone&starttime=$starttime&numdone=$numdone\">Click here to continue</a> if the Javascript redirect doesn't work.</p>\n";
  }
?>

</body>
</html>
