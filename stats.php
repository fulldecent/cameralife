<?php

/**Stats provides fun statistics about the number of photos in the system and the most popular photos and albums.
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 * @todo Pass a stats object
 */

require 'main.inc';
$features = array('theme', 'security');
$cameralife = CameraLife::cameraLifeWithFeatures($features);
$stats = new Stats($cameralife);
$cameralife->theme->showPage('stats');
