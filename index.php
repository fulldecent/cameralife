<?php
namespace CameraLife;

/**
 * Sets up the environment and routes every request
 *
 * @author    William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
 * @access    public
 */

// Bail if autoload is not available!
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    throw new Exception('Camera Life requires PHP version 5.4 or higher.');
}

// Set up environment
define('CAMERALIFE_VERSION', '2.7.0a6');
define('BASE_URL', 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/'));
define('BASE_DIR', dirname(__FILE__));
require 'vendor/autoload.php';
require 'sources/autoload.php';

// Load configuration
if (file_exists(dirname(__FILE__) . '/config/config.php')) {
    include dirname(__FILE__) . '/config/config.php';
}

// Route the page request
//TODO: maybe pass in config arguments through this function
Controllers\Controller::handleRequest($_GET, $_POST, $_FILES, $_COOKIE, $_SERVER);
