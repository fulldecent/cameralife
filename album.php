<?php
/**Displays an album page
*@link http://fdcl.sourceforge.net
*@version 2.6.2
*@author Will Entriken <cameralife@phor.net>
*@copyright © 2001-2009 Will Entriken
*@access public
*/

/**
*/
  $features=array('database','theme');
  require "main.inc";

  $album = new Album($_GET['id']);
  $album->ShowPage();
?>
