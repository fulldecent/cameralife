<?php

/**
 * Displays the main page
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2014 William Entriken
 * @access public
 */
 
require 'main.inc';
$features = array('theme', 'fileStore');
$cameralife = CameraLife::cameraLifeWithFeatures($features);
$cameralife->theme->showPage('index');
