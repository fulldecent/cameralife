<?php
/**
*Displays topic name on the page
*@link http://fdcl.sourceforge.net
*@version 2.6.3b4
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */
/**
*/
  $features=array('database','theme');
  require "main.inc";

  $topic = new Topic($_GET['name']);
  $topic->ShowPage();
?>
