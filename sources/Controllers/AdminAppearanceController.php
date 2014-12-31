<?php
namespace CameraLife\Controllers;
use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Displays the Admin Appearance page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */

class AdminAppearanceController extends HtmlController
{
    public $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Models\Statistics;
        $this->title = 'Site stats';
        $this->icon = 'bar-chart';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
            throw new \Exception('You are not authorized to view this page');
        }

        /* Set up the page view */
        $view = new Views\AdminPreferencesView;
        $view->moduleName = 'Site Appearance';

        $preferences = array();
        $preferences[] = ['module' => 'CameraLife', 'key' => 'sitename', 'type' => 'string', 'name' => 'Site name'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'owner_email', 'type' => 'string', 'name' => 'Owner email address'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'rewrite', 'type' => 'yesno', 'name' => 'Use pretty URLs'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'autorotate', 'type' => 'yesno', 'name' => 'Autorotate photos'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'thumbsize', 'type' => 'number', 'name' => 'Size for thumbnails'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'scaledsize', 'type' => 'number', 'name' => 'Size for preview images'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'optionsizes', 'type' => 'string', 'name' => 'Other available sizes', 'help' => 'comma separated (you can also leave this blank)'];
        $view->preferences = $preferences;

        $this->htmlHeader($cookies);
        $view->render();
        $this->htmlFooter();
    }
}
