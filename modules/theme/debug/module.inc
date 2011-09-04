<?php
/**
*@link http://fdcl.sourceforge.net/
  *@version 
  *@author Will Entriken <cameralife@phor.net>
  *@access public
  *@copyright Copyright (c) 2001-2009 Will Entriken
*/
/**Carries function that notifies debugging is in process 
*/
  class DebugTheme extends ThemeModule
  {
    function DebugTheme() 
    {
      $this->preferences = array();
    }

    function ShowPage($title, $item = NULL)
    {
      echo '<html><head><title>Debug</title></head><body>';
      echo '<h1>Debugging: '.$title.'</h1><pre>';
      var_dump($item);
      echo '</pre></body></html>';
    }
  }
?>
