<?php
  $features=array('database','theme');
  require "main.inc";

  $album = new Album($_GET['id']);
  $album->ShowPage();
?>
