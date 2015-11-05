<?php
namespace CameraLife\Controllers;

use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Displays the Search page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class LogoutController extends HtmlController
{
    public function __construct($modelId)
    {
        parent::__construct();
        $this->title = 'Login';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        Models\User::logoutCurrentUser();
        header('Location: ' . MainPageController::getUrl());
    }
}
