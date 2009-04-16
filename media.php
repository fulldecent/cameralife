<?php
  #
  # Displays a photo
  # The file is got from the photostore "the hard" way and printed out
  # This file makes asset security possible since the user does not directly access the photos.
  #
  # Required GET variables: id, format (one of 'photo', 'thumbnail', 'scaled'), ver (mtime)
  #
  /**Displays a photo.
  *
  *This file makes asset security possible since the user does not directly access the photos.
  *The file is requested from the photostore and returned to the browser.
  *
  *<b>Note:</b>Alternatively the photostore could have sent the client to another URL to request the
  *file directly but that would be a less secured method.
  *Required GET variables
  *<ul>
  *<li>id</li>
  *<li>format - 'photo', 'thumbnail'or 'scaled')</li>
  *<li>ver (mtime)</li>
  *@link http://fdcl.sourceforge.net
  *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */
  /**
  */

  $features=array('database','security','imageprocessing', 'photostore');
  require "main.inc";

  $photo = new Photo($_GET['id']);
  $format = $_GET['format'];
  if (!is_numeric($_GET['ver'])) $cameralife->Error('Required number ver missing! Expected a number, got: '.htmlentities($_GET['ver']));
  $extension = $photo->extension;

  if (!$cameralife->Security->authorize('admin_file'))
  {
    if ($photo->Get('status')==1) $reason = "deleted";
    elseif ($photo->Get('status')==2) $reason = "marked as private";
    elseif ($photo->Get('status')==3) $reason = "uploaded but not revied";
    elseif ($photo->Get('status')==!0) $reason = "marked non-public";
    if ($reason) $cameralife->Error("Photo access denied: $reason");
  }

  if ($format == 'photo')
    list($file, $temp, $mtime) = $cameralife->PhotoStore->GetFile($photo, $format);
  elseif ($format == 'scaled')
    list($file, $temp, $mtime) = $cameralife->PhotoStore->GetFile($photo, $format);
  elseif ($format == 'thumbnail')
    list($file, $temp, $mtime) = $cameralife->PhotoStore->GetFile($photo, $format);
  else
    $cameralife->Error('Bad format parameter');

  if ($extension == 'jpg' || $extension == 'jpeg')
    header('Content-type: image/jpeg');
  elseif ($extension == 'png')
    header('Content-type: image/png');
  else
    $cameralife->Error('Unknown file type');

  header('Content-Disposition: inline; filename="'.htmlentities($photo->Get('description')).'.'.$extension.'";');
# header('Cache-Control: '.($photo['status'] > 0) ? 'private' : 'public');
  header('Content-Length: '.filesize($file));

  header("Date: ".gmdate("D, d M Y H:i:s", $mtime)." GMT");
  header("Last-Modified: ".gmdate("D, d M Y H:i:s", $mtime)." GMT");
  header("Expires: ".gmdate("D, d M Y H:i:s", time() + 2592000)." GMT"); // One month

  readfile($file);
  if ($temp) unlink($file);
?>
