<?php
namespace CameraLife;

/**
 * Displays all albums for a given topic
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */

require 'main.inc';
$features = array('theme');
$cameralife = CameraLife::cameraLifeWithFeatures($features);
$topic = new Topic($_GET['name']);

if (!isset($_GET['edit']) && $topic->getAlbumCount() == 0) {
    header("HTTP/1.0 404 Not Found");
    $cameralife->error("This folder does not exist.");
}

$topic->showPage();
