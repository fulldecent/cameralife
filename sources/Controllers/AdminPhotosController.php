<?php
namespace CameraLife\Controllers;

use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Displays the Admin Review Photos Page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */

class AdminPhotosController extends HtmlController
{
    public function __construct()
    {
        parent::__construct();
        $this->title = 'Review New photos';
        $this->icon = 'list';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
            throw new \Exception('You are not authorized to view this page');
        }

        /* Set up the page view */
        $checkpointId = intval(Models\Preferences::valueForModuleWithKey('CameraLife', 'checkpointphotos'));
        
        $view = new Views\AdminPhotosView;
        $view->isUsingHttps = isset($_SERVER['HTTPS']);
        $view->myUrl = $_SERVER['REQUEST_URI'];
        
        $query = Models\Database::select('photos', 'id', 'id>:0 AND status!=9', 'ORDER BY id LIMIT 200', null, array($checkpointId));
        $view->photos = array();
        while ($row = $query->fetchAssoc()) {
            $view->photos[] = Models\Photo::getPhotoWithID($row['id']);
            $view->lastReviewItem = $row['id'];
        }

        $done = Models\Database::selectOne('photos', 'count(id)', 'id<=:0 AND status!=9', null, null, array($checkpointId));
        $view->reviewsDone = $done;
        $remaining = Models\Database::selectOne('photos', 'count(id)', 'id>:0 AND status!=9', null, null, array($checkpointId));
        $view->reviewsRemaining = $remaining;

        $this->htmlHeader($cookies);
        $view->render();
        $this->htmlFooter();
    }
}
