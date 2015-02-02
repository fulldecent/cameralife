<?php
namespace CameraLife\Controllers;

use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Displays a tag page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */

class TagController extends HtmlController
{
    private $model;

    public function __construct($modelId)
    {
        parent::__construct($modelId);
        $this->model = new Models\Tag($modelId);
        $this->title = $this->model->query;
        $this->icon = 'tag';

        $photo = $this->model->getPoster();
        $this->image = $photo->getMediaURL('thumbnail');
        $this->imageType = 'image/jpeg';
        $this->imageWidth = $photo->get('tn_width');
        $this->imageHeight = $photo->get('tn_height');
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        /* Set up common page parts */
        $this->htmlHeader($cookies);

        $this->model->set('hits', $this->model->get('hits') + 1);

        /* Set up breadcrumbs */
        $breadcrumbs = new Views\BreadcrumbView;
        $tagCollection = $this->model->getTagCollection();
        $breadcrumbs->openGraphObjects[] = new TagCollectionController($tagCollection->query);
        $breadcrumbs->openGraphObjects[] = $this;
        $breadcrumbs->render();

        /* Set up grid */
        $start = isset($get['start']) ? $get['start'] : 0;
        $objects = array();
        foreach ($this->model->getPhotos() as $photo) {
            $objects[] = new PhotoController($photo->id);
        }

        /* Set up grid */
        $grid = new Views\GridView;
        $grid->openGraphObjects = $objects;
        $grid->render();

        /* Set up page selector */
        $pageSelector = new Views\PageSelectorView;
        $pageSelector->start = $start;
        $pageSelector->total = $this->model->getPhotoCount();
        $pageSelector->render();

        /* Render footer */
        $this->htmlFooter();
    }
}
