<?php
/*
 * Administer files on the site
 * BEWARE: BACKUP YOUR DATABASE BEFORE MESSING AROUND HERE!
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 Will Entriken
 * @access public
 */
$features=array('security', 'photostore', 'imageprocessing');
require '../main.inc';
chdir ($cameralife->base_dir);
$cameralife->base_url = dirname($cameralife->base_url);
$cameralife->Security->authorize('admin_customize', 1); // Require
$_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 'flagged';
@ini_set('max_execution_time',9000); // for rescan

$curphoto = FALSE;
foreach ($_POST as $key=>$val) {
  list($cmd, $id) = explode('_', $key);
  if ($id == FALSE) continue;
  if (!$curphoto || $id != $curphoto->Get('id')) {
    $curphoto = new Photo($id);
  }
  switch ($cmd) {
  case 'desc':
    if ($curphoto->Get('description') != $val && strlen($val) > 0) {
      if ($val == 'ERASED')
        $curphoto->Erase();
      else
        $curphoto->Set('description', $val);
    }
    break;
  case 'key':
    if ($curphoto->Get('keywords') != $val);
      $curphoto->Set('keywords', $val);
    break;
  case 'rot':
    if ($val != 0)
      $curphoto->Rotate($val);
    break;
  case 'stat':
    if ($curphoto->Get('status') != $val);
      $curphoto->Set('status', $val);
    break;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Camera Life - Hacks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.no-icons.min.css" rel="stylesheet">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>
  </head>
  <body>
    <div class="navbar navbar-inverse navbar-static-top">
      <div class="container">
        <span class="navbar-brand"><a href="../"><?= $cameralife->GetPref("sitename") ?></a> / Hacks</span>
      </div>
    </div>
    <div class="container">
      <form method="post">
        <p class="lead">Photos without a name set are shown below, you can quickly edit unnamed photos.</p>
<?php

  $search = new Search('unnamed');
#  $search->mySearchPhotoCondition = "status=$target_status OR 0";
  $search->SetPage(0, 24);
  $photos = $search->GetPhotos();
  $icons = array();

  echo '<div class="thumbnails">';
  $i=0;
  foreach ($photos as $photo) {
    $icon = $photo->GetIcon();
    $target_status = $photo->Get('status');
    echo '<div class="col-sm-2"><div class="thumbnail text-center">';
    echo '<a href="'.$icon['href'].'">';
    echo '<img id="img_'.$photo->Get('id').'" src="'.$icon['image'].'"></a>';
/*
    echo '<select class="form-control" style="width:100%" name="'.$photo->Get('id').'">'.
                      '<option value="0" '.($target_status==0?'selected':'').'>Public</option>'.
                      '<option value="1" '.($target_status==1?'selected':'').'>Flagged</option>'.
                      '<option value="2" '.($target_status==2?'selected':'').'>Private</option>'.
                      '<option value="3" '.($target_status==3?'selected':'').'>New Upload</option>'.
                      '<option value="4" '.($target_status==4?'selected':'').'>Erased</option></select><br>';
*/
    echo '<input class="form-control" id="desc_'.$photo->Get('id').'" name="desc_'.$photo->Get('id').'" value="'.$photo->Get('description').'">';
/*
    echo '<div class="btn-group btn-group-justified" data-toggle="buttons-radio">
    <a type="button" name="toggle'.$photo->Get('id').'" data-value="" class="btn btn-default myButton"><i class="icon-shield"></i></a>
    <a type="button" name="toggle'.$photo->Get('id').'" class="btn btn-default myButton"><i class="icon-shield icon-rotate-90"></i></a>
    <a type="button" name="toggle'.$photo->Get('id').'" class="btn btn-default myButton"><i class="icon-shield icon-rotate-180"></i></a>
    <a type="button" name="toggle'.$photo->Get('id').'" class="btn btn-default myButton"><i class="icon-shield icon-rotate-270"></i></a>
</div>';
*/
    echo "<input checked type=radio id=\"rot0_".$photo->Get('id')."\" name=\"rot_".$photo->Get('id')."\" value=\"0\" onclick=\"document.getElementById('img_".$photo->Get('id')."').src='rotatethumb.php?id=".$photo->Get('id')."&amp;rotate=0'\">";
    echo "<label for=\"rot1_".$photo->Get('id')."\"><i class='icon-shield'></i></label> ";
    echo "<input type=radio id=\"rot90_".$photo->Get('id')."\" name=\"rot_".$photo->Get('id')."\" value=\"90\" onclick=\"document.getElementById('img_".$photo->Get('id')."').src='rotatethumb.php?id=".$photo->Get('id')."&amp;rotate=90'\">";
    echo "<label for=\"rot90_".$photo->Get('id')."\"><i class='icon-shield icon-rotate-90'></i></label> ";
    echo "<input type=radio id=\"rot180_".$photo->Get('id')."\" name=\"rot_".$photo->Get('id')."\" value=\"180\" onclick=\"document.getElementById('img_".$photo->Get('id')."').src='rotatethumb.php?id=".$photo->Get('id')."&amp;rotate=180'\">";
    echo "<label for=\"rot180_".$photo->Get('id')."\"><i class='icon-shield icon-rotate-180'></i></label> ";
    echo "<input type=radio id=\"rot270_".$photo->Get('id')."\" name=\"rot_".$photo->Get('id')."\" value=\"270\" onclick=\"document.getElementById('img_".$photo->Get('id')."').src='rotatethumb.php?id=".$photo->Get('id')."&amp;rotate=270'\">";
    echo "<label for=\"rot270_".$photo->Get('id')."\"><i class='icon-shield icon-rotate-270'></i></label><br>";
/*
    echo "<a href=\"../photo.php&#63;id=".$photo['id']."\">";
    echo "<img id=\"img_".$photo['id']."\" src='../media.php&#63;scale=thumbnail&amp;id=".$photo['id'].'&amp;ver='.$photo['mtime']."'></a>";
    echo "<td width=\"50%\">";
    echo "<input id=\"desc_".$photo['id']."\" name=\"desc_".$photo['id']."\" value=\"".$photo['description']."\"><br>";
    echo "<input id=\"key_".$photo['id']."\" name=\"key_".$photo['id']."\" value=\"".$photo['keywords']."\"><br> ";
    echo "<input checked type=radio id=\"rot0_".$photo['id']."\" name=\"rot_".$photo['id']."\" value=\"0\" onclick=\"document.getElementById('img_".$photo['id']."').src='rotatethumb.php?id=".$photo['id']."&amp;rotate=0'\">";
    echo "<label for=\"rot0_".$photo['id']."\">N</label>";
    echo "<input type=radio id=\"rot90_".$photo['id']."\" name=\"rot_".$photo['id']."\" value=\"90\" onclick=\"document.getElementById('img_".$photo['id']."').src='rotatethumb.php?id=".$photo['id']."&amp;rotate=90'\">";
    echo "<label for=\"rot90_".$photo['id']."\">R</label>";
    echo "<input type=radio id=\"rot180_".$photo['id']."\" name=\"rot_".$photo['id']."\" value=\"180\" onclick=\"document.getElementById('img_".$photo['id']."').src='rotatethumb.php?id=".$photo['id']."&amp;rotate=180'\">";
    echo "<label for=\"rot180_".$photo['id']."\">U</label>";
    echo "<input type=radio id=\"rot270_".$photo['id']."\" name=\"rot_".$photo['id']."\" value=\"270\" onclick=\"document.getElementById('img_".$photo['id']."').src='rotatethumb.php?id=".$photo['id']."&amp;rotate=270'\">";
    echo "<label for=\"rot270_".$photo['id']."\">L</label><br>";
    $checked = ($photo['status']==0)?'checked':'';
    echo "<input $checked type=radio id=\"stat0_".$photo['id']."\" name=\"stat_".$photo['id']."\" value=\"0\">";
    echo "<label for=\"stat0_".$photo['id']."\">Public</label>";
    $checked = ($photo['status']==2)?'checked':'';
    echo "<input $checked type=radio id=\"stat2_".$photo['id']."\" name=\"stat_".$photo['id']."\" value=\"2\">";
    echo "<label for=\"stat2_".$photo['id']."\">Private</label><br>";
    echo "<input type=button value=\"Erase\" onclick=\"document.getElementById('desc_".$photo['id']."').value='ERASED'\">\n";
    echo "<input type=button value=\"Reset\" onclick=\"document.getElementById('desc_".$photo['id']."').value='".$photo['description']."'\">\n";
*/

    echo '</div></div>';
    if (++$i%6==0) echo '</div><div class="thumbnails" style="clear:left">';
  }
  //$total = $cameralife->Database->SelectOne('photos','COUNT(*)',"status=$target_status");
  echo '</div>';
?>
<p style="clear:left">
  <input type=submit value="Commit Changes" class="btn btn-danger">
  <a href="<?= '?'.$_SERVER['QUERY_STRING'] ?>" class="btn">Undo changes</a>
</p>
      </form>
    </div>
  </body>
</html>
