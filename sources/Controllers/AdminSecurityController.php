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

class AdminSecurityController extends HtmlController
{
    public $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Models\Statistics;
        $this->title = 'Comments viewer';
        $this->icon = 'list';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
            throw new \Exception('You are not authorized to view this page');
        }

        /* Set up the page view */
        $view = new Views\AdminSecurityView;

        $users = array();
        $query = Models\Database::select('users', '*', '', 'ORDER by id');
        while ($record = $query->fetchAssoc()) {
            $users[] = new Models\User($record['id']);
        }
        $view->users = $users;

        $policies = array();
        $view->securityPolicies = $policies;

        $this->htmlHeader($cookies);
        $view->render();
        $this->htmlFooter();
    }
}
