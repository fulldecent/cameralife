<?php
namespace CameraLife;

/**
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */

require 'main.inc';
$features = array('theme', 'security', 'fileStore');
$cameralife = CameraLife::cameraLifeWithFeatures($features);

if (!Photo::photoExists($_GET['id'])) {
    header("HTTP/1.0 404 Not Found");
    $cameralife->error("Photo #" . intval($_GET['id']) . " not found.");
}

$photo = Photo::getPhotoWithID($_GET['id']);
if ($photo->get('status') != 0) {
    $cameralife->security->authorize('admin_file', 'This file has been flagged or marked private');
}
$photo->set('hits', $photo->get('hits') + 1);

$photo->showPage();
