<?php
  # Handles a POST FORM when all you want to do is set some site preferences.
  # Then redirects to the target page

  # Pass me variables like this:
  # module1 = core
  # param1 = site_name
  # value1 = Camera Life
  # target = admin/customize.php
 
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
    if ($pref['module'] && $pref['param'] && $pref['value'])
    {
      $cameralife->preferences[$pref['module']][$pref['param']] = $pref['value'];
      $cameralife->SavePreferences();
    }
    else 
      die ('passed wrong');
  }

  if (!isset($_POST['target']))
    die ('Error: no target set!');

  header("Location: ".$_POST['target']);
?>
