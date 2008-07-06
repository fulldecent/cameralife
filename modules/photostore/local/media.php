<?php
  # Returns an original photo, or scaled photo or thumbnail.
  # This file makes asset security possible since the user does not directly access the photos.
  $features=array('database','security','imageprocessing', 'photostore');
  require "../../../main.inc";
  $cameralife->base_url = dirname(dirname(dirname($cameralife->base_url)));

  $photo = new Photo($_GET['id']);
  $format = $_GET['format'];
  if (!is_numeric($_GET['ver'])) $cameralife->Error('Required number ver missing! Expected a number, got: '.$_GET['ver']);
  $path_parts = pathinfo($photo->Get('filename'));
  $extension = strtolower($path_parts['extension']);

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
      $file = $cameralife->base_dir .'/'. $cameralife->PhotoStore->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension;
    else
      $file = $cameralife->base_dir .'/'. $cameralife->PhotoStore->GetPref('photo_dir') .'/'. $photo->Get('path').$photo->Get('filename');
  }
  elseif ($format == 'scaled')
    $file = $cameralife->base_dir .'/'. $cameralife->PhotoStore->GetPref('cache_dir').'/'.$photo->Get('id').'_600.'.$extension;
  elseif ($format == 'thumbnail')
    $file = $cameralife->base_dir .'/'. $cameralife->PhotoStore->GetPref('cache_dir').'/'.$photo->Get('id').'_150.'.$extension;
  else
    $cameralife->Error('Bad format parameter');

  if (!file_exists($file))
  {
    if ($format == 'photo')
      $cameralife->Error("The photo <b>$file</b> cannot be found", __FILE__, __LINE__);
    $photo->GenerateThumbnail();
  }

  if ($extension == 'jpg' || $extension == 'jpeg')
    header('Content-type: image/jpeg');
  elseif ($extension == 'png')
    header('Content-type: image/png');
  else
    $cameralife->Error('Unknown file type');
  header('Content-Disposition: inline; filename="'.htmlentities($photo->Get('description')).'.'.$extension.'";');
# header('Cache-Control: '.($photo['status'] > 0) ? 'private' : 'public');
  header('Content-Length: '.filesize($file));

  header("Date: ".gmdate("D, d M Y H:i:s", filemtime($file))." GMT");
  header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($file))." GMT");
  header("Expires: ".gmdate("D, d M Y H:i:s", time() + 2592000)." GMT"); // One month

  readfile($file);
?>
