<?php

/**Displays the folders path file on the page
@link http://fdcl.sourceforge.net
*@version 2.6.2
*@author Will Entriken <cameralife@phor.net>
*@copyright © 2001-2009 Will Entriken
*@access public
*/
/**
*/
  $features=array('database','theme','photostore', 'imageprocessing');
  require "main.inc";

  $folder = new Folder($_GET['path'], TRUE);
  $folder->ShowPage();
?>
