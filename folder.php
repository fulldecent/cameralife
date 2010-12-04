<?php

/**Displays the folders path file on the page
 *@link http://fdcl.sourceforge.net
 *@version 2.6.3
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 */

$features=array('database','theme','photostore', 'imageprocessing', 'security');
require "main.inc";

##TODO: make the second param true there
$folder = new Folder(stripslashes($_GET['path']), false);

$count = array_sum($folder->GetCounts());
if ($count == 0) {
  header("HTTP/1.0 404 Not Found");
  $cameralife->Error("This folder does not exist, or it is empty.");
}

$folder->ShowPage();
?>
