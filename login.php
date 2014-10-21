<?php
namespace CameraLife;

/**
 * Enables log in
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */

$features = array('theme');
require 'main.inc';
$cameralife->theme->showPage('login');
