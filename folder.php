<?php
  $features=array('database','theme','photostore');
  require "main.inc";

  $folder = new Folder($_GET['path'], TRUE);
  $folder->ShowPage();
?>
