<?php
  $features=array('database','theme');
  require "main.inc";

  $photo = new Photo($_GET['id']);
  $photo->ShowPage();
?>
