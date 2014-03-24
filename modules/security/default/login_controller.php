<?php
/*
 * Handles the LOGIN form directed from login.php
 *
 * Accepts these POST parameters
 * <ul>
 *  <li>action = login or register</li>
 *  <li>param1 = username</li>
 *  <li>param2 = password</li>
 *  <li>param3 = email (optional)</li>
 *  <li>target = where to go afterwards or 'ajax'</li>
 * </ul>
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
*/

$features=array('security');
require '../../../main.inc';
$cameralife->baseURL = dirname(dirname(dirname($cameralife->baseURL)));
if (get_class($cameralife->security) != 'DefaultSecurity')
  $cameralife->error("Can't access this page because the current security module is ".get_class($cameralife->security));

if (strtolower($_POST['action']) == 'login') {
  $result = $cameralife->security->Login($_POST['param1'], $_POST['param2']);
  if (is_string($result))
    $cameralife->error($result);
} elseif (strtolower($_POST['action']) == 'register') {
  $result = $cameralife->security->Register($_POST['param1'], $_POST['param2'], $_POST['param3']);
  if (is_string($result))
    $cameralife->error($result);
}

if ($_POST['target'] == 'ajax')
  exit(0);
else
  header("Location: ".$_POST['target']);
