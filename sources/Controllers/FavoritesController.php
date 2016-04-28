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
class FavoritesController extends HtmlController
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->title = 'My favorite photos';
        $this->icon = 'star';
        $this->url = self::getUrl();
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        $this->model = Models\Favorites::favoritesForCurrentUser($cookies);
        $start = isset($get['start']) ? $get['start'] : 0;
        $this->model->setPage($start);

        /* Set up common page parts */
        $this->htmlHeader($cookies);

		if (count($this->model->getPhotos())) {
	        $view = new Views\BackgroundBlurView;
	        $view->imageURL = $this->model->getPhotos()[0]->getMediaURL('thumbnail');
	        $view->render();
		}

        $photoCount = $this->model->getPhotoCount();
        
        foreach ($this->model->getPhotos() as $photo) {
            $gridObjects[] = new PhotoController($photo->id);
        }

        //TODO: breaks MVC
        echo '<h2>My favorite photos</h2>';

        /* Set up grid */
        $grid = new Views\GridView;
        $grid->openGraphObjects = $gridObjects;
        $grid->render();

        /* Set up page selector */
        $pageSelector = new Views\PageSelectorView;
        $pageSelector->start = $start;
        $pageSelector->total = $photoCount;
        $pageSelector->render();

        /* Render footer */
        $this->htmlFooter();
    }
}
