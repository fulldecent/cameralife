<?php
namespace CameraLife\Controllers;

use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Displays the main page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
 * @access public
 */

class TagCollectionController extends HtmlController
{
    private $model;

    public function __construct($id)
    {
        parent::__construct($id);
        $this->model = new Models\TagCollection($id);
        $this->title = $this->model->query;
        $this->icon = 'tags';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        /* Set up common page parts */
        $this->htmlHeader($cookies);

        /* Set up breadcrumbs */
        $breadcrumbs = new Views\BreadcrumbView;
        $breadcrumbs->openGraphObjects[] = $this;
        $breadcrumbs->render();

        /* Set up grid */
        $start = isset($get['start']) ? $get['start'] : 0;
        $obs = array();
        foreach ($this->model->getTags() as $tag) {
            $obs[] = new TagController($tag->record['id']);
        }

        /* Set up grid */
        $grid = new Views\GridView;
        $grid->openGraphObjects = $obs;
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
