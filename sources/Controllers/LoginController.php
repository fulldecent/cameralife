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
class LoginController extends HtmlController
{
    public function __construct($modelId)
    {
        parent::__construct();
        $this->title = 'Login';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        /* Set up common page parts */
        $this->htmlHeader($cookies);
        ?>
        <h3 class="panel-title">Login</h3>
        <form method="post">
          <div class="form-group row">
            <label for="accesscode" class="col-sm-2 col-form-label col-form-label-lg">Access code</label>
            <div class="col-sm-10">
              <input type="password" class="form-control form-control-lg" id="accesscode" name="accesscode">
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-lg">Sign in</button>
        </form>
<?php

        /* Render footer */
        $this->htmlFooter();
    }

    public function handlePost($get, $post, $files, $cookies)
    {
        \CameraLife\Models\User::loginWithAccessCode($post['accesscode']);
        header('Location: ' . MainPageController::getUrl());
        return;
    }
}
