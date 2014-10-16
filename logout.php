<?php
namespace CameraLife;

require 'main.inc';
$features = array('security');
$cameralife = CameraLife::cameraLifeWithFeatures($features);
$url = $cameralife->security->Logout();

if (is_string($url)) {
    header('Location: ' . $url);
} else {
    header('Location: ' . $cameralife->baseURL . '/index.php');
}
