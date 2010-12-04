<?php
/** Setting up the theme
*
  *@link http://fdcl.sourceforge.net
  *@version 2.6.3
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */
  /**
  */
  /**The base class for themes
 */ 

  class ThemeModule extends CameraLifeModule
  {
    function ThemeModule() {}

    /**
    *  Displays all HTML to the user given the specified name and associated data
    *
    *  @param string $name the name of the page
    *  @param mixed $object any extra data
    */
    function ShowPage($name, $object=NULL) {}
  }
?>
