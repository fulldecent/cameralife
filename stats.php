<?php
/**Stats provides fun statistics about the number of photos in the system and the most popular photos and albums.
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 *@todo Pass a stats object
 */

$features=array('theme', 'security');
require 'main.inc';

$stats = new Stats;
$cameralife->theme->ShowPage('stats');
