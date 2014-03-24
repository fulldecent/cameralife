<?php
/**
 * Retrieve a photo from the FileStore and feed it to the user
 * This file makes asset security possible since the user does not directly access the photos.
 *
 * This gets linked to from Photo::getMedia() when a FileStore::getURL() returns FALSE
 * You should understand that before continuing.
 *
 * Required GET variables
 * <ul>
 *  <li>id</li>
 *  <li>scale - ('photo', 'thumbnail', or 'scaled')</li>
 *  <li>ver (mtime)</li>
 * </ul>
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2014 William Entriken
 * @access public
 */

$features = array('security', 'imageProcessing', 'fileStore');
require 'main.inc';

$photo = new Photo(intval($_GET['id']));
$format = isset($_GET['scale']) ? $_GET['scale'] : null;
if (!is_numeric($_GET['ver'])) {
    $cameralife->error('Required number ver missing! Query string: ' . htmlentities($_SERVER['QUERY_STRING']));
}

$extension = $photo->extension;

if (!$cameralife->security->authorize('admin_file')) {
    $reason = null;
    if ($photo->get('status') == 1) {
        $reason = "deleted";
    } elseif ($photo->get('status') == 2) {
        $reason = "marked as private";
    } elseif ($photo->get('status') == 3) {
        $reason = "uploaded but not revied";
    } elseif ($photo->get('status') == !0) {
        $reason = "marked non-public";
    }
    if ($reason) {
        $cameralife->error("Photo access denied: $reason");
    }
}

if ($format == 'photo' || $format == '') {
    if ($photo->get('modified')) {
        list($file, $temp, $mtime) = $cameralife->fileStore->GetFile(
            'other',
            '/' . $photo->get('id') . '_mod.' . $extension
        );
    } else {
        $fullpath = rtrim('/' . ltrim($photo->get('path'), '/'), '/') . '/' . $photo->get('filename');
        list($file, $temp, $mtime) = $cameralife->fileStore->GetFile('photo', $fullpath);
    }
} elseif ($format == 'scaled') {
    list($file, $temp, $mtime) = $cameralife->fileStore->getFile(
        'other',
        '/' . $photo->get('id') . '_' . $cameralife->getPref('scaledsize') . '.' . $extension
    );
} elseif ($format == 'thumbnail') {
    list($file, $temp, $mtime) = $cameralife->fileStore->GetFile(
        'other',
        '/' . $photo->get('id') . '_' . $cameralife->getPref('thumbsize') . '.' . $extension
    );
}
elseif (is_numeric($format)) {
    $valid = preg_split('/[, ]+/', $cameralife->getPref('optionsizes'));
    if (in_array($format, $valid)) {
        list($file, $temp, $mtime) = $cameralife->fileStore->GetFile(
            'other',
            '/' . $photo->get('id') . '_' . $format . '.' . $extension
        );
    } else {
        $cameralife->error('This image size has not been allowed');
    }
} else {
    $cameralife->error('Bad size parameter. Query string: ' . htmlentities($_SERVER['QUERY_STRING']));
}

if ($extension == 'jpg' || $extension == 'jpeg') {
    header('Content-type: image/jpeg');
} elseif ($extension == 'png') {
    header('Content-type: image/png');
} elseif ($extension == 'gif') {
    header('Content-type: image/gif');
} else {
    $cameralife->error('Unknown file type');
}

header('Content-Disposition: inline; filename="' . htmlentities($photo->get('description')) . '.' . $extension . '";');
# header('Cache-Control: '.($photo['status'] > 0) ? 'private' : 'public');
header('Content-Length: ' . filesize($file));

header("Date: " . gmdate("D, d M Y H:i:s", $mtime) . " GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s", $mtime) . " GMT");
header("Expires: " . gmdate("D, d M Y H:i:s", time() + 2592000) . " GMT"); // One month

readfile($file);
if ($temp) {
    unlink($file);
}
