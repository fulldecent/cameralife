<?php

/**Displays the folders path file on the page
 *@link http://fdcl.sourceforge.net
 *@version 2.6.3b4
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 */

$features=array('database','theme','photostore', 'imageprocessing', 'security');
require "main.inc";

##TODO: make the second param true there
$folder = new Folder(stripslashes($_GET['path']), false);
$folder->ShowPage();
?>
