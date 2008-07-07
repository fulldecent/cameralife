<?php
  $features=array('database','theme');
  require "main.inc";

  $search = new Search($_GET['q']);
  $search->ShowPage();
?>
