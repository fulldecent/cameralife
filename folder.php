<?php
namespace CameraLife;

/**
 * Displays the folders path file on the page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */

require 'main.inc';
$features = array('theme', 'fileStore', 'imageProcessing', 'security');
$cameralife = CameraLife::cameraLifeWithFeatures($features);
$folder = new Folder($_GET['path']);

if ($folder->getPhotoCount() + $folder->getFolderCount() == 0) {
    header("HTTP/1.0 404 Not Found");
    $cameralife->error("This folder does not exist, or it is empty.");
}

$folder->showPage();
