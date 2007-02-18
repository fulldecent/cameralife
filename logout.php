<?php
  $features=array('security');
  require "main.inc";

  $url = $cameralife->Security->Logout();

  if (is_string($url))
    header('Location: '.$url);
  else
    header('Location: '.$cameralife->base_url.'/index.php');
?>
