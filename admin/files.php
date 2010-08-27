<?php
/**
 * Handle file things
 * BEWARE: BACKUP YOUR DATABASE BEFORE MESSING AROUND HERE!
 * if only I had told myself that earlier today :-(
 *
 * @link http://fdcl.sourceforge.net
 * @version 2.6.3b4
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */
@ini_set('max_execution_time',9000);

$features=array('database','security','imageprocessing', 'photostore');
require "../main.inc";
$cameralife->base_url = dirname($cameralife->base_url);
chdir ($cameralife->base_dir);
$cameralife->Security->authorize('admin_file', 1); // Require

$_GET['page'] or $_GET['page'] = 'flagged';

// Handle form actions
foreach ($_POST as $key=>$val)
{
  if (!is_int($key)) continue;
  $curphoto = new Photo($key);
  if ($val>=0 && $val<=3)
    $curphoto->Set('status', $val);
  else // Erased file
    $curphoto->Erase();
}

?>
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <link rel="stylesheet" href="admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
  <script language="javascript">
    function changeall() {
      val = document.getElementById('status').value;
      inputs = document.getElementsByTagName('select');
      for (var i = 0; i < inputs.length; i++) {
          inputs[i].value=val;
      }
    }
  </script>
</head>
<body>

<div id="header">
<h1>Site Administration &ndash; File Manager</h1>
<?php
  $home = $cameralife->GetIcon('small');
  echo '<a href="'.$home['href']."\"><img src=\"".$cameralife->IconURL('small-main')."\">".$home['name']."</a>\n";
?> |
<a href="index.php"><img src="<?= $cameralife->IconURL('small-admin')?>">Site Administration</a>
</div>

<form method="post" action="http://<?= $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'] ?>&#63;page=<?= $_GET['page']?>">
<h2>
  Show:
  <a href="?page=flagged">Flagged photos</a> |
  <a href="?page=private">Private photos</a> |
  <a href="?page=update">Rescan photos</a>
</h2>
<?php

  if ($_GET['page'] == 'flagged')
    $target_status = 1;
  else if ($_GET['page'] == 'private')
    $target_status = 2;
  else if ($_GET['page'] == 'upload')
    $target_status = 3;

  if ($_GET['page'] !== 'update') // Show stuff
  {
    if ($_GET['page'] == 'flagged')
      echo "<p class='administrative'>Photos that have been flagged will show up here. If you 'erase' a photo, if will be deteled from your photostore. Some photostores will keep a copy of deleted photos. You can configure photostores <a href='photostore.php'>here</a>. You may send flagged photos to the private photo section.</p>";
    else if ($_GET['page'] == 'private')
      echo '<p class="administrative">Photos that have been marked private will show here.</p>';
    else if ($_GET['page'] == 'upload')
      echo '<p class="administrative">Photos that have been uploaded by users will show here.</p>';

    echo '<p>Change All: <select name="status" onchange="changeall()" id="status">';
    echo '<option value="0" '.($target_status==0?'selected':'').'>Public</option>';
    echo '<option value="1" '.($target_status==1?'selected':'').'>Flagged</option>';
    echo '<option value="2" '.($target_status==2?'selected':'').'>Private</option>';
    echo '<option value="3" '.($target_status==3?'selected':'').'>New Upload</option>';
    echo '<option value="4" '.($target_status==4?'selected':'').'>Erased</option>';
    echo '</select></p>';

    $search = new Search('');
    ### @todo You know code is a hack when you use a SQL injection attack against yourself.
    $search->mySearchPhotoCondition = "status=$target_status OR 0";
    $search->SetPage(0, 9999);
    $photos = $search->GetPhotos();
    $icons = array();

    echo '<table>';

    foreach($photos as $photo)
    {
      if (!($i++%4)) echo '<tr>';

      $icon = $photo->GetIcon();
      echo '<td align="center" width="25%">';
      echo '<a href="'.$icon['href'].'">';
      echo '<img src="'.$icon['image'].'"></a><br />';
      echo '<select name="'.$photo->Get('id').'">'.
                        '<option value="0" '.($target_status==0?'selected':'').'>Public</option>'.
                        '<option value="1" '.($target_status==1?'selected':'').'>Flagged</option>'.
                        '<option value="2" '.($target_status==2?'selected':'').'>Private</option>'.
                        '<option value="3" '.($target_status==3?'selected':'').'>New Upload</option>'.
                        '<option value="4" '.($target_status==4?'selected':'').'>Erased</option></select><br>'.
                        $icon['name'];
    }

    $total = $cameralife->Database->SelectOne('photos','COUNT(*)',"status=$target_status");
    echo '</table>';
?>
<p>
  <input type=submit value="Commit Changes">
  <a href="<?= $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'] ?>">(Undo changes)</a>
</p>
<?php
  }
  else // Update DB
  {
    echo "<p>Updating the database to reflect any changes to tue PhotoStore...</p>\n<ol>\n";
    flush();

    $output = Folder::Update();
    foreach($output as $line)
      echo "<li>$line</li>\n";

    echo "</ol>\n<p>Updating complete :-) Now you can:<ul>\n";
    echo "<li><a href=\"../search.php?q=unnamed\">Name your new files</a></li>\n";
    echo "<li><a href='thumbnails.php'>Optimize thumbnails</a></li>\n";
    echo "</ul>\n";
  }
?>
  </table>
  </form>
</body>
</html>

