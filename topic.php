<?php
  $features=array('database','theme');
  require "main.inc";

  $topic = new Topic($_GET['name']);
  $topic->ShowPage();
?>
