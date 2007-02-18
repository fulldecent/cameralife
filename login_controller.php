<?php
  # Handle the LOGIN form action from login.php
  #
  # Pass me variables:
  # action = login or register
  # param1 = username
  # param2 = password
  # param3 = email (optional)
  # target = where to go afterwards or 'ajax'

  $features=array('database','theme','security');
  require "main.inc";

  if (strtolower($_POST['action']) == 'login')
  {
    $result = $cameralife->Security->Login($_POST['param1'], $_POST['param2']);
    if (is_string($result))
      $cameralife->Error($result);
  }
//todo bad
  elseif(strtolower($_POST['action']) == 'register')
  {
    $result = $cameralife->Security->Register($_POST['param1'], $_POST['param2'], $_POST['param3']);
    if (is_string($result))
      $cameralife->Error($result);
  }

  if ($_POST['target'] == 'ajax')
    exit(0);
  else
    header("Location: ".$_POST['target']);
?>