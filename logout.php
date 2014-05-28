<?php
namespace CameraLife;
/**A security feature that enables you log out of CameraLife
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */
/**
 */

$features = array('security');
require 'main.inc';

$url = $cameralife->security->Logout();

if (is_string($url)) {
    header('Location: ' . $url);
} else {
    header('Location: ' . $cameralife->baseURL . '/index.php');
}
