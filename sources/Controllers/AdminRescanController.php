<?php
namespace CameraLife\Controllers;

use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Finds new photos in the system
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */

class AdminRescanController extends HtmlController
{
    public function __construct()
    {
        parent::__construct();
        $this->title = 'Rescan photos';
        $this->icon = 'search';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
            throw new \Exception('You are not authorized to view this page');
        }
        
        $results = Models\Folder::update();
        
        /* Set up the page view */
        $view = new Views\AdminRescanView;
        $view->scanResults = $results;
        $view->thumbnailUrl = AdminThumbnailController::getUrl();

        $this->htmlHeader($cookies);
        $view->render();
        $this->htmlFooter();
    }
}
