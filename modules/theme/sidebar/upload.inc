<?php

  /**Enables photo upload
  *
  *<ul>
   * <li>You can upload a photo in JPEG format or a ZIP of JPEG photos</li>
   * <li>The maximum file size is 20MB</li>
  *</ul>
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */
  /**
  */
 #$cameralife set by ShowPage()

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');
  $cameralife->GetFeature('imageprocessing');

  if (isset($_REQUEST['path']) && $_REQUEST['path'] != 'upload/'.$user['username'].'/')
  {
    $cameralife->Security->Authorize('admin_file', 1);
    $path = $_REQUEST['path'];
  }
  else
  {
    $cameralife->Security->Authorize('photo_upload', 1);
    $path = 'upload/'.$cameralife->Security->GetName().'/';
  }
  $folder = new Folder($path);
  $icon = $folder->GetIcon();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
</head>
<body>
<BGSOUND src="filedone.wav">
<form enctype="multipart/form-data" method=POST action="upload_controller.php">
<input type="hidden" name="target" value="<?= $icon['href'] ?>">
<?php
  $menu = array();

  $menu[] = array("name"=>$cameralife->GetPref('siteabbr'),
                  "href"=>"index.php",
                  'image'=>'small-main');

  $menu[] = array("name"=>"View folders",
                  "href"=>"folder.php?page=folder&path=",
                  'image'=>'small-folder');
  if ($_REQUEST['path'])
  {
    $menu[] = array("name"=>'My uploaded photos',
                    "href"=>"folder.php?path=".$_REQUEST['path'],
                    'image'=>'small-folder');
}

  $cameralife->Theme->TitleBar('Upload photos','photo',FALSE,$menu);


  $cameralife->Theme->Section('Uploading Photos');
?>
  <ul>
    <li>You can upload a photo in JPEG format or a ZIP of JPEG photos
    <li>The maximum file size is 20MB
  </ul>

  <?php
    if ($cameralife->Security->Authorize('admin_file') && !isset($_REQUEST['path']))
    {
      $cameralife->Theme->Section('Note');
      echo "You can also upload to individual folders.";
      echo "<ul>";
      echo "  <li>Go to the folder you would like to upload to";
      echo "  <li>Choose Upload photo here from the title menu link";
      echo "</ul>";
    }
  ?>

  <input type="hidden" name="MAX_FILE_SIZE" value="20800000">
  <table>
    <tr><td>Path:<td><?= $path ?><input name="path" type=hidden value="<?= $path ?>">
    <tr><td>Photo file:<td><input name="userfile" type="file">
    <tr><td>Photo description:<td><input name="description" type="text" size=50>
  </table>
  <input type="submit" value="Upload Photo">

</form>
</body>
</html>
