<?php
/**
 * Displays an album page
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

$features = array('theme');
require 'main.inc';

$album = new Album($_GET['id']);
$album->set('hits', $album->get('hits') + 1);

$album->showPage();
