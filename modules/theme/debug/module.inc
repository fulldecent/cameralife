<?php
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
