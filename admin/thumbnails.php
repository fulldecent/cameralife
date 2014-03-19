<?php
/*
 * Regenerates thumbnail caches
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 Will Entriken
 * @access public
 */
$features=array('security', 'filestore', 'imageprocessing');
@ini_set('max_execution_time',9000);
require '../main.inc';
$cameralife->Security->authorize('admin_customize', 1); // Require
$cameralife->base_url = dirname($cameralife->base_url);
chdir ($cameralife->base_dir);
require 'admin.inc';
$cameralife->Security->authorize('admin_file', 1); // Require
$lastdone = isset($_GET['lastdone']) ? (int) $_GET['lastdone'] : 0;
$starttime = isset($_GET['starttime']) ? (int) $_GET['starttime'] : time();
$numdone = isset($_GET['numdone']) ?(int) $_GET['numdone'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Admin: Thumbnails</title> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="generator" content="Camera Life version <?= $cameralife->version ?>">
    <meta name="author" content="<?= $cameralife->GetPref('owner_email') ?>">

    <!-- Le styles -->
    <link href="<?= $cameralife->base_url ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css" rel="stylesheet">
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>

    <div class="navbar navbar-inverse navbar-static-top">
      <div class="container">
        <span class="navbar-brand"><a href="../"><?= $cameralife->GetPref("sitename") ?></a> / Administration</span>
      </div>
    </div>
    
    <div class="container">
      <h2>Update thumbnails</h2>
      <p>We are now caching thumbnails. This avoids a delay when a photo is viewed for the first time.</p>

<?php
  $total = $cameralife->Database->SelectOne('photos', 'count(*)');
  $done = $cameralife->Database->SelectOne('photos', 'count(*)', "id <= $lastdone");
  $todo = $cameralife->Database->SelectOne('photos', 'count(*)', "id > $lastdone");
  $timeleft = ceil((time()-$starttime) * $todo / ($numdone + $done/1000 + 1) / 60);

  echo "<p>Progress: $done of $total done";
  if ($done != $total)
    echo " (about $timeleft minutes left)";
  echo "</p>\n";
  echo '<div class="progress">';
  echo '<div class="progress-bar" style="width: '.($done/$total*100).'%;"></div>';
  echo '</div>';

  $next1000 = $cameralife->Database->Select('photos', 'id', "id > $lastdone", 'ORDER BY id LIMIT 1000');
  $fixed = 0;
  flush();
  while (($next = $next1000->FetchAssoc()) && ($fixed < 10)) {
    $curphoto = new Photo($next['id']);
    if ($cameralife->FileStore->CheckThumbnails($curphoto)) {
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
