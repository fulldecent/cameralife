<?php
  $features=array('database','theme');
  require "main.inc";

  $folder = new Folder($_GET['path'], $true);
  $folder->ShowPage();
?>
