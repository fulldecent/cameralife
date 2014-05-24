<?php
/**
 * Handles login POST event from login.php
 *
 * Form variables:
 *  action        "login" or "register"
 *  target        "ajax" or a URL for the next view to load
 *  OTHER         as defined by the security module
 *
 * The current security module will have access to the full POST variables
 * since it is also responsible for the login view.
 *
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */

$features = array('theme', 'security');
require 'main.inc';

if (strtolower($_POST['action']) == 'login') {
    $result = $cameralife->security->login($_POST['username'], $_POST);
    if (is_string($result)) {
        $cameralife->error($result);
    }
} elseif (strtolower($_POST['action']) == 'register') {
    $result = $cameralife->security->register($_POST['username'], $_POST);
    if (is_string($result)) {
        $cameralife->error($result);
    }
}

if ($_POST['target'] == 'ajax') {
    exit(0);
} else {
    header("Location: " . $_POST['target']);
}
