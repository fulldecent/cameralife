<?php
/**
 * Retrieve a photo from the PhotoStore and feed it to the user
 * This file makes asset security possible since the user does not directly access the photos.
 *
 * This gets linked to from Photo::GetMedia() when a PhotoStore::GetURL() returns FALSE
 * You should understand that before continuing.
 *
 * Required GET variables
 * <ul>
 *  <li>id</li>
 *  <li>scale - ('photo', 'thumbnail', or 'scaled')</li>
 *  <li>ver (mtime)</li>
 * </ul>
 * @link http://fdcl.sourceforge.net
 * @version 2.6.2
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

$features=array('database','security','imageprocessing', 'photostore');
require "main.inc";

$photo = new Photo($_GET['id']);
$format = $_GET['scale'];
if (!is_numeric($_GET['ver'])) 
  $cameralife->Error('Required number ver missing! Query string: '.htmlentities($_SERVER['QUERY_STRING']));

$extension = $photo->extension;

if (!$cameralife->Security->authorize('admin_file'))
{
  if ($photo->Get('status')==1) $reason = "deleted";
  elseif ($photo->Get('status')==2) $reason = "marked as private";
  elseif ($photo->Get('status')==3) $reason = "uploaded but not revied";
  elseif ($photo->Get('status')==!0) $reason = "marked non-public";
  if ($reason) $cameralife->Error("Photo access denied: $reason");
}

  //TODO add a really rool hack here:
  //  if photostore==local && fmt==thumbnail && doesnotexist && extension==jpeg && modified == 0orNULL && exif(rotate)==none
  //  then resizetosizeofThumbnail(imagecreatefromstring(readexif(file)))
  //
  //  and if ... && exif(rotate)>1
  //  then rotate and serve.
  //
  // however, this code may belong in localphotostore, so he can cache it

if ($format == 'photo' || $format == '')
  list($file, $temp, $mtime) = $cameralife->PhotoStore->GetFile($photo, 'photo');
elseif ($format == 'scaled')
  list($file, $temp, $mtime) = $cameralife->PhotoStore->GetFile($photo, $format);
elseif ($format == 'thumbnail')
  list($file, $temp, $mtime) = $cameralife->PhotoStore->GetFile($photo, $format);
elseif (is_numeric($format))
{
  $valid = preg_split('/[, ]+/',$cameralife->GetPref('optionsizes'));
  if (in_array($format, $valid))
    list($file, $temp, $mtime) = $cameralife->PhotoStore->GetFile($photo, $format);
  else
    $cameralife->Error('This image size has not been allowed');
}
else
  $cameralife->Error('Bad size parameter. Query string: '.htmlentities($_SERVER['QUERY_STRING']));

if ($extension == 'jpg' || $extension == 'jpeg')
  header('Content-type: image/jpeg');
elseif ($extension == 'png')
  header('Content-type: image/png');
elseif ($extension == 'gif')
  header('Content-type: image/gif');
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
