<?php

/**
@link http://fdcl.sourceforge.net
*@version 2.6.2
*@author Will Entriken <cameralife@phor.net>
*@copyright © 2001-2009 Will Entriken
*@access public
*/
/**
*/
  $features=array('database','theme');
  require "main.inc";

  $photo = new Photo($_GET['id']);
  $photo->ShowPage();
?>
