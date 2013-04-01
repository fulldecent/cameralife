<?php
/*
 * Give feedback on Camera Life
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */
$features=array('database','security', 'photostore');
require '../main.inc';
$cameralife->base_url = dirname($cameralife->base_url);
$cameralife->Security->authorize('admin_customize', 1); // Require
$stats = new Stats;
$counts = $stats->GetCounts();
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
  </head>

  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <span class="brand"><a href="../"><?= $cameralife->GetPref("sitename") ?></a> / <a href="index.php">Administration</a> / Give Feedback</span>
        </div>
      </div>
    </div>
    <div class="container">
      <h1>Please send this email:</h1>
      <pre>
From: <?= $cameralife->GetPref('owner_email') ?>

To:   cameralife@phor.net
Subj: CAMERALIFE-FEEDBACK

To the Camera Life team,

I have set up a site named <strong><?= $cameralife->GetPref('sitename') ?></strong> at <strong><?= str_replace('admin','',$cameralife->base_url) ?></strong> based on the <strong><?= $cameralife->version ?></strong> version of Camera Life. It has been up for <strong><?= $counts['daysonline'] ?></strong> days, contains <strong><?= $counts['photos'] ?></strong> photos and is running on <strong><?= preg_replace('/server.*/i','',preg_replace('|<[^>]*>|', '', $_SERVER['SERVER_SIGNATURE'])) ?></strong>.

You may/may not (pick one!) list my site on the Camera Life software demo page. I have some comments about the software, namely:

<i><u><strong>comments</strong></u></i>

Cheers,
<strong>Your Name</strong>
<?= $cameralife->GetPref('owner_email') ?>
      </pre>
    </div>
  </body>
</html>
