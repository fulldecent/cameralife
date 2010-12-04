<?php
/**
 * Handles a POST FORM and lets you set/edit the site preferences and then redirects you to the target page
 *
 * Pass variables such as the following
 * <ul>
 * <li>module1 = CameraLife</li>
 * <li>param1 = site_name</li>
 * <li> value1 = Camera Life</li>
 * <li>target = admin/customize.php</li>
 * </ul>
 * @link http://fdcl.sourceforge.net
 * @version 2.6.2
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */
$features=array('database','theme','security');
require "../main.inc";
$cameralife->base_url = dirname($cameralife->base_url);

$cameralife->Security->authorize('admin_customize', 1); // Require
$prefs = array();

foreach ($_POST as $key => $val)
{
  if (strpos($key, 'module') === 0)
    $prefs[substr($key, 6)]['module'] = $val;
  elseif (strpos($key, 'param') === 0)
    $prefs[substr($key, 5)]['param'] = $val;
  elseif (strpos($key, 'value') === 0)
    $prefs[substr($key, 5)]['value'] = $val;
}

foreach ($prefs as $pref)
{
  if (isset($pref['module']) && isset($pref['param']) && isset($pref['value']))
  {
    $cameralife->userpreferences[$pref['module']][$pref['param']] = $pref['value'];
    $cameralife->SavePreferences();
  }
  else
    die ('passed wrong');
}

if (!isset($_POST['target']))
  die ('Error: no target set!');

header("Location: ".$_POST['target']);
?>
