<?php
  # Returns a JPEG photo, or scaled photo or thumbnail.
  # This file makes asset security possible since the user does not directly access the photos.
  $features=array('database','security','imageprocessing');
  require "main.inc";

  $photo = new Photo($_GET['id']);
  $format = $_GET['format'];
  if ($_GET['ver'] === NULL) $cameralife->Error('Required argument ver missing!');

  if (!$cameralife->Security->authorize('admin_file'))
  {
    if ($photo->Get('status')==1) $reason = "deleted";
    elseif ($photo->Get('status')==2) $reason = "marked as private";
    elseif ($photo->Get('status')==3) $reason = "uploaded but not revied";
    elseif ($photo->Get('status')==!0) $reason = "marked non-public";
    if ($reason) $cameralife->Error("Photo access denied: $reason");
  }

  if ($format == 'photo')
  {
    if ($photo->Get('modified'))
      $file = $cameralife->preferences['core']['cache_dir'] .'/'. $photo->Get('id').'_mod.jpg';
    else
      $file = $cameralife->preferences['core']['photo_dir'] .'/'. $photo->Get('path').$photo->Get('filename');
  }
  elseif ($format == 'scaled')
    $file = $cameralife->preferences['core']['cache_dir'].'/'.$photo->Get('id').'_600.jpg';
  elseif ($format == 'thumbnail')
    $file = $cameralife->preferences['core']['cache_dir'].'/'.$photo->Get('id').'_150.jpg';
  else
    $cameralife->Error('Bad format parameter');

  if (!file_exists($file))
  {
    if ($format == 'photo')
      $cameralife->Error("The photo <b>$file</b> cannot be found");
    $photo->GenerateThumbnail();
  }

  header('Content-type: image/jpeg');
  header('Content-Disposition: inline; filename='.htmlentities($photo->Get('description')).'.jpg');
# header('Cache-Control: '.($photo['status'] > 0) ? 'private' : 'public');
  header('Content-Length: '.filesize($file));
#  header('Date: '.filemtime($file));

  header("Date: ".gmdate("D, d M Y H:i:s", filemtime($file))." GMT");
  header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($file))." GMT");
  header("Expires: ".gmdate("D, d M Y H:i:s", time() + 2592000)." GMT"); // One month

  readfile($file);
?>
