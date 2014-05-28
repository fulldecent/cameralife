<?php
namespace CameraLife;
/**
 * Handles various POST actions from admin views
 *
 * @uses $_POST['target'] REQUIRED "ajax" or a URL for the next view to load
 * @uses $_POST['MODULE|PARAM'] REQUIRED sets a new value for MODULE's PARAM
 *
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */

$features = array('security');
require '../main.inc';
$cameralife = CameraLife::cameraLifeWithFeatures($features);
$cameralife->baseURL = dirname($cameralife->baseURL);
$cameralife->security->authorize('admin_customize', 1); // Require
$prefs = array();

foreach ($_POST as $key => $val) {
    if ($key == 'target') {
        continue;
    } else {
        $array = explode('|', $key);
        if (count($array) != 2) {
            $cameralife->error('Invalid module / key');
        }
        $prefs[] = array('module' => $array[0], 'param' => $array[1], 'value' => $val);
    }
}

foreach ($prefs as $pref) {
    if (isset($pref['module']) && isset($pref['param']) && isset($pref['value'])) {
        $cameralife->userpreferences[$pref['module']][$pref['param']] = $pref['value'];
        $cameralife->savePreferences();
    } else {
        var_dump($prefs);
        die ('passed wrong');
    }
}

if (!isset($_POST['target'])) {
    die ('error: no target set!');
}

header("Location: " . $_POST['target']);
