<?php
/*
 * Administer files on the site
 * BEWARE: BACKUP YOUR DATABASE BEFORE MESSING AROUND HERE!
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 Will Entriken
 * @access public
 */
$features=array('security', 'filestore');
require '../main.inc';
chdir ($cameralife->base_dir);
$cameralife->base_url = dirname($cameralife->base_url);
$cameralife->Security->authorize('admin_customize', 1); // Require
$_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 'flagged';
@ini_set('max_execution_time',9000); // for rescan

// Handle form actions
foreach ($_POST as $key=>$val) {
  if (!is_int($key)) continue;
  $curphoto = new Photo($key);
  if ($val>=0 && $val<=3)
    $curphoto->set('status', $val);
  else // Erased file
    $curphoto->erase();
}
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
      <ul class="nav nav-tabs">
        <li<?= ($_GET['page']=='flagged')?' class="active"':'' ?>><a href="?page=flagged">Flagged files</a></li>
        <li<?= ($_GET['page']=='private')?' class="active"':'' ?>><a href="?page=private">Private</a></li>
        <li<?= ($_GET['page']=='update')?' class="active"':'' ?>><a href="?page=update">Rescan files</a></li>
      </ul>
  <?php

if ($_GET['page'] == 'flagged')
  $target_status = 1;
else if ($_GET['page'] == 'private')
  $target_status = 2;
else if ($_GET['page'] == 'upload')
  $target_status = 3;

if ($_GET['page'] !== 'update') { // Show stuff
?>
      <div class="well well-sm">
        <h2>Quick tools</h2>
        <p>Set all status to...</p>
        <select name="status" onchange="$('select').val($('#status').val())" id="status">';
          <option value="0">Public</option>
          <option value="1">Flagged</option>
          <option value="2">Private</option>
          <option value="3">New Upload</option>
          <option value="4">Erased</option>
        </select>
      </div>
      <form method="post">
<?php
  if ($_GET['page'] == 'flagged')
    echo "<p class=\"lead\">Photos show up here when flagged. \"Erasing\" a photo deletes it.";
  else if ($_GET['page'] == 'private')
    echo '<p>Photos that have been marked private will show here.</p>';
  else if ($_GET['page'] == 'upload')
    echo '<p>Photos that have been uploaded by users will show here.</p>';

  $search = new Search('');
  $search->mySearchPhotoCondition = "status=$target_status OR 0";
  $search->setPage(0, 9999);
  $photos = $search->getPhotos();
  echo '<div class="thumbnails">';
  $i=0;
  foreach ($photos as $photo) {
    $photoOpenGraph = $photo->GetOpenGraph();
    echo '<div class="col-sm-2"><div class="thumbnail text-center">';
    echo '<a href="'.htmlspecialchars($photoOpenGraph['og:url']).'">';
    echo '<img src="'.htmlspecialchars($photoOpenGraph['og:image']).'"></a><br />'.htmlentities($photoOpenGraph['og:title']);
    echo '<select style="width:100%" name="'.$photo->Get('id').'">'.
                      '<option value="0" '.($target_status==0?'selected':'').'>Public</option>'.
                      '<option value="1" '.($target_status==1?'selected':'').'>Flagged</option>'.
                      '<option value="2" '.($target_status==2?'selected':'').'>Private</option>'.
                      '<option value="3" '.($target_status==3?'selected':'').'>New Upload</option>'.
                      '<option value="4" '.($target_status==4?'selected':'').'>Erased</option></select><br>';
    echo '</div></div>';
    if (++$i%6==0) echo '</div><div class="thumbnails">';
  }
  //$total = $cameralife->Database->SelectOne('photos','COUNT(*)',"status=$target_status");
  echo '</div>';
?>
<p>
  <input type=submit value="Commit Changes" class="btn btn-danger">
  <a href="<?= '?'.$_SERVER['QUERY_STRING'] ?>" class="btn">Undo changes</a>
</p>
<?php
} else { // Update DB
  echo "<p>Updating the database to reflect any changes to the FileStore...</p>\n<ol>\n";
  flush();

  $output = Folder::update();
  foreach($output as $line)
    echo "<li>$line</li>\n";

  echo "</ol>\n<p>Updating complete :-) Now you can:<ul>\n";
  echo "<li><a href=\"../search.php?q=unnamed\">Name your new files</a></li>\n";
  echo "<li><a href='thumbnails.php'>Optimize thumbnails</a></li>\n";
  echo "</ul>\n";
}
?>
      </form>
    </div>
  </body>
</html>
