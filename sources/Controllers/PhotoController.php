<?php
namespace CameraLife\Controllers;
use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Displays the Folder page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class PhotoController extends HtmlController
{
    private $model;

    public function __construct($id)
    {
        parent::__construct();

        if (!Models\Photo::photoExists(intval($id))) {
            header("HTTP/1.0 404 Not Found");
            $cameralife->error("Photo #" . intval($_GET['id']) . " not found.");
        }

        $this->model = Models\Photo::getPhotoWithID($id);
        $this->title = $this->model->get('description');
        $this->icon = 'photo';
        $this->url = self::getUrlForID($this->model->id); //todo: done by parent?

        $this->imageType = 'image/jpeg';
        $this->image = $this->model->getMediaURL('thumbnail');
        $this->imageType = 'image/jpeg';
        $this->imageWidth = $this->model->get('tn_width');
        $this->imageHeight = $this->model->get('tn_height');
    }

    public function handleGet($get, $post, $files, $cookies)
    {
// todo, get PREV and NEXT links from photo and use meta prev/next in HTML theme header

        $view = new Views\PhotoView;
        $view->photo = $this->model;
        $view->currentUser = Models\User::currentUser($cookies);

        if (isset($get['referrer'])) {
            $view->referrer = $get['referrer'];
        } else if (isset($_SERVER['HTTP_REFERER'])) {
            $view->referrer = $_SERVER['HTTP_REFERER'];
        }

        /* Set up common page parts */
        $this->htmlHeader($cookies);

        if ($this->model->get('status') != 0) {
            $cameralife->security->authorize('admin_file', 'This file has been flagged or marked private');
        }
        $this->model->set('hits', $this->model->get('hits') + 1);

        $view->render();

        /* Render footer */
        $this->htmlFooter();
    }

    public function handlePost($get, $post, $files, $cookies)
    {
        $currentUser = Models\User::currentUser($cookies);

        switch ($post['action']) {
            case 'favorite':
                $this->model->favoriteByUser($currentUser);
                break;
            case 'unfavorite':
                $this->model->unfavoriteByUser($currentUser);
                break;
        }

        parent::handlePost($get, $post, $files, $cookies);
    }
}
