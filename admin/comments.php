<?php
/*
 * Administer comments on the site
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */
$features=array('database','security', 'photostore');
require "../main.inc";
$cameralife->base_url = dirname($cameralife->base_url);
$cameralife->Security->authorize('admin_customize', 1); // Require

if (!$_POST['showme'] && !$_POST['showreg'] && !$_POST['showunreg']) {
  $_POST['showme'] = TRUE;
  $_POST['showreg'] = TRUE;
  $_POST['showunreg'] = TRUE;
}
if ($_POST['action'] == 'Delete checked') {
  foreach ($_POST as $var => $val) {
    if (!is_numeric($var) || !is_numeric($val))
      continue;
    $cameralife->Database->Delete('comments',"id=$var");
  }
}
$numcomments = $cameralife->Database->SelectOne('comments','COUNT(*)','id>'.($cameralife->GetPref('checkpointcomments')+0));
$checkpointDate = strtotime($cameralife->Database->SelectOne('comments','max(date)','id='.($cameralife->GetPref('checkpointcomments')+0)));
$latestComment = $cameralife->Database->SelectOne('comments','max(id)');
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
          <span class="brand"><a href="../"><?= $cameralife->GetPref("sitename") ?></a> / <a href="index.php">Administration</a> / Comments</span>
        </div>
      </div>
    </div>
    <div class="container">
      <h1>Options</h1>
<?php
if ($checkpointDate) {
?>
      <form class="alert alert-info" method="post" action="controller_prefs.php">
        A checkpoint was set on <?= date("Y-m-d",$checkpointDate) ?>. Only comments after then are shown.
        <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'] ?>" />
        <input type="hidden" name="module1" value="CameraLife" />
        <input type="hidden" name="param1" value="checkpointcomments" />
        <input type="hidden" name="value1" value="0">
        <input class="btn" type="submit" value="Reset checkpoint">
        <a href="https://github.com/fulldecent/cameralife/wiki/Checkpoints" class="btn"><i class="icon-info-sign"></i> Learn about checkpoints</a>
      </form>
<?php
} else {
?>
      <form class="alert alert-info" method="post" action="controller_prefs.php">
        No checkpoint is set. All comments are being shown.
        <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'] ?>" />
        <input type="hidden" name="module1" value="CameraLife" />
        <input type="hidden" name="param1" value="checkpointcomments" />
        <input type="hidden" name="value1" value="<?= $latestComment ?>">
        <input class="btn" type="submit" value="Hide comments up to now">
        <a href="https://github.com/fulldecent/cameralife/wiki/Checkpoints" class="btn"><i class="icon-info-sign"></i> Learn about checkpoints</a>
        </form>
<?php
}
?>
      <form class="well form-inline" method="post">
        Show comments from:
        <label class="checkbox inline">
         <input type="checkbox" name="showme" <?php if ($_POST["showme"]) echo " checked" ?>> 
         <i class="icon-user"></i> Me
        </label>
        <label class="checkbox inline">
         <input type="checkbox" name="showreg" <?php if ($_POST["showreg"]) echo " checked" ?>> 
         <i class="icon-user"></i> Registered users
        </label>
        <label class="checkbox inline">
         <input type="checkbox" name="showunreg" <?php if ($_POST["showunreg"]) echo " checked" ?>> 
         <i class="icon-user"></i> Unregistered users
        </label>
        <input class="btn" type=submit value="Update">
      </form>
      <div class="pull-right well">
        <h1>Quick tools</h1>
        <button class="btn" onclick="$('#comments :checkbox').slice(0,10).attr('checked',true)">Check the first 10 checkboxes</button><br/>
        <button class="btn" onclick="$('#comments :checkbox').slice(0,50).attr('checked',true)">Check the first 50 checkboxes</button><br/>
        <button class="btn" onclick="$('#comments :checkbox').slice(0,200).attr('checked',true)">Check the first 200 checkboxes</button><br/>
        <button class="btn" onclick="$('#comments blockquote :contains(http://)').closest('label').children('input').attr('checked',true)">Check all with http://</button>
      </div>
      <h1>Comments</h1> 
      <form method="post" class="form" id="comments">
  <?php
    $condition = "(0 ";
    if ($_POST['showme'])
      $condition .= "OR username = '".$cameralife->Security->GetName()."' ";
    if ($_POST['showreg'])
      $condition .= "OR (username LIKE '_%' AND username != '".$cameralife->Security->GetName()."')";
    if ($_POST['showunreg'])
      $condition .= "OR username = '' ";
    $condition .= ") ";

    $checkpoint = $cameralife->Database->SelectOne('comments','MAX(id)');
    echo "<input type='hidden' name='checkpoint' value='$checkpoint'>\n";

    $condition .= " AND id > ".($cameralife->GetPref('checkpointcomments')+0);
    $extra = "GROUP BY photo_id ORDER BY id DESC";

    $result = $cameralife->Database->Select('comments','*, MAX(id) as maxid',$condition,$extra);
    while($record = $result->FetchAssoc())
    {
    //var_dump($record);
    
      $photo = new Photo($record['photo_id']);
      $icon = $photo->GetIcon('small');
      $icon = $photo->GetIcon();
      //echo "<P>";var_dump($icon);echo "</p>";
      $max = max($icon['width'], $icon['height']);
      $width64 = $icon['width'] / $max * 64;
      $height64 = $icon['height'] / $max * 64;
?>
        <div class="media">
          <a class="pull-left" style="width: 64px" href="<?= $icon['href'] ?>">
            <img class="media-object" data-src="holder.js/64x64" alt="thumbnail" style="width: <?= $width64?>px; height: <?= $height64 ?>px;" src="<?= $icon['image'] ?>">
          </a>
          <div class="media-body">
            <h4 class="media-heading"><?= htmlentities($icon['name']) ?></h4>
<?php            
      $condition = "photo_id = ".$record['photo_id'];
      $result2 = $cameralife->Database->Select('comments','*',$condition, 'ORDER BY id DESC');

      while ($row = $result2->FetchAssoc())
      {
        $byLine = ($row['username']?$row['username']:'Anonymous').' ('.$row['user_ip'].') '.$row['user_date'];
?>
            <label class="checkbox">
              <input type="checkbox" name="<?= $row['id'] ?>" value="<?= $row['id'] ?>">
              <blockquote>
                <p><?= htmlentities($row['comment']) ?></p>
                <small><?= $byLine ?></small>
              </blockquote>
            </label>
<?php
      }
?>          
          </div>
        </div>
<?php
    }
?>
        <p>
          <input class="btn btn-danger" type=submit name="action" value="Delete checked">
          <a class="btn" href="?">Revert to last saved</a><br>
        </p>
      </form>
    </div>
  </body>
</html>
