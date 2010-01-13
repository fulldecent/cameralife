<?php
/**Stats provides fun statistics about the number of photos in the system and the most popular photos and albums.
*@link http://fdcl.sourceforge.net
  *@version 2.6.3b4
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  *@todo Pass a stats object
  */

/**
*/

  $features=array('database','theme', 'security');
  require "main.inc";

  $cameralife->Theme->ShowPage('stats');
#TODO pass a stats object?
?>
