<?php
/**
* Displays an album page
* @link http://fdcl.sourceforge.net
* @version 2.6.3b6
* @author Will Entriken <cameralife@phor.net>
* @copyright Copyright (c) 2001-2009 Will Entriken
* @access public
*/

$features=array('database','theme');
require "main.inc";

$album = new Album($_GET['id']);
$album->Set('hits', $album->Get('hits') + 1);

$album->ShowPage();
?>
