<?php
  $features=array('database','theme','photostore', 'imageprocessing');
  require "main.inc";

  $folder = new Folder($_GET['path'], TRUE);
  $folder->ShowPage();
?>
