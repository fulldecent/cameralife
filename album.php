<?php
/**
 * Displays an album page
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */

$features = array('theme');
require 'main.inc';

$album = new Album($_GET['id']);
$album->set('hits', $album->get('hits') + 1);

$album->showPage();
