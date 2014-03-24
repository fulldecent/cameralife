<?php
/*
 * Regenerates thumbnail caches
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 William Entriken
 * @access public
 */
$features = array('security', 'fileStore', 'imageProcessing');
@ini_set('max_execution_time', 9000);
require '../main.inc';
$cameralife->security->authorize('admin_customize', 1); // Require
$cameralife->baseURL = dirname($cameralife->baseURL);
chdir($cameralife->baseDir);
require 'admin.inc';
$cameralife->security->authorize('admin_file', 1); // Require
$lastdone = isset($_GET['lastdone']) ? (int)$_GET['lastdone'] : 0;
$starttime = isset($_GET['starttime']) ? (int)$_GET['starttime'] : time();
$numdone = isset($_GET['numdone']) ? (int)$_GET['numdone'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin: Thumbnails</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="generator" content="Camera Life version <?= $cameralife->version ?>">
    <meta name="author" content="<?= $cameralife->getPref('owner_email') ?>">

    <!-- Le styles -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css" rel="stylesheet">
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>

<div class="navbar navbar-inverse navbar-static-top">
    <div class="container">
        <span class="navbar-brand"><a href="../"><?= $cameralife->getPref("sitename") ?></a> / Administration</span>
    </div>
</div>

<div class="container">
    <h2>Update thumbnails</h2>

    <p>We are now caching thumbnails. This avoids a delay when a photo is viewed for the first time.</p>

    <?php
    $total = $cameralife->database->SelectOne('photos', 'count(*)');
    $done = $cameralife->database->SelectOne('photos', 'count(*)', "id <= $lastdone");
    $todo = $cameralife->database->SelectOne('photos', 'count(*)', "id > $lastdone");
    $timeleft = ceil((time() - $starttime) * $todo / ($numdone + $done / 1000 + 1) / 60);

    echo "<p>Progress: $done of $total done";
    if ($done != $total) {
        echo " (about $timeleft minutes left)";
    }
    echo "</p>\n";
    echo '<div class="progress">';
    echo '<div class="progress-bar" style="width: ' . ($done / $total * 100) . '%;"></div>';
    echo '</div>';

    $next1000 = $cameralife->database->Select('photos', 'id', "id > $lastdone", 'ORDER BY id LIMIT 1000');
    $fixed = 0;
    flush();
    while (($next = $next1000->fetchAssoc()) && ($fixed < 10)) {
        $curphoto = new Photo($next['id']);
        if ($cameralife->fileStore->CheckThumbnails($curphoto)) {
            echo "<div>Updated #" . $next['id'] . "</div>\n";
            flush();
            $fixed++;
        }
        $curphoto->destroy();
        $lastdone = $next['id'];
    }

    $numdone += $fixed;
    if ($todo > 0) {
        echo "<script language='javascript'>window.setTimeout('window.location=\"" . $_SERVER['PHP_SELF'] . "?lastdone=$lastdone&starttime=$starttime&numdone=$numdone\"',600)</script>\n";
        echo "<p><a href=\"thumbnails.php?lastdone=$lastdone&starttime=$starttime&numdone=$numdone\">Click here to continue</a> if the Javascript redirect doesn't work.</p>\n";
    }
    ?>

</body>
</html>
