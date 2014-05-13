<?php

/**
 * Displays an album page
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */

require 'main.inc';
$features = array('theme');
$cameralife = CameraLife::cameraLifeWithFeatures($features);
$album = new Album($_GET['id']);
$album->set('hits', $album->get('hits') + 1);

$album->showPage();
