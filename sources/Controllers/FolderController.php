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
class FolderController extends HtmlController
{
    private $model;

    public function __construct($id = '/')
    {
        parent::__construct();
        $this->model = new Models\Folder($id);
        $this->title = basename($this->model->path);
        $this->icon = 'folder';
        $this->url = self::getUrlForID($this->model->path);
        $this->image = constant('BASE_URL') . '/assets/folder.png';
        $this->imageType = 'image/png';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        $start = isset($get['start']) ? $get['start'] : 0;
        $section = isset($get['section']) ? $get['section'] : NULL;
        $this->model->setPage($start);
        $photoCount = $this->model->getPhotoCount();
        $folderCount = $this->model->getFolderCount();
        $gridObjects = array();
        if (!$photoCount || $section == 'folders') {
            foreach($this->model->getFolders() as $folder) {
                $gridObjects[] = new FolderController($folder->id);
            }
        } else {
            foreach($this->model->getPhotos() as $photo) {
                $gridObjects[] = new PhotoController($photo->id);
            }
        }

        /* Set up common page parts */
        $this->htmlHeader($cookies);

        /* Set up breadcrumbs */
        $breadcrumbs = new Views\BreadcrumbView;
        foreach($this->model->getAncestors() as $ancestor) {
            $openGraph = new FolderController($ancestor->path);
            $openGraph->title = basename($openGraph->title);
            $breadcrumbs->openGraphObjects[] = $openGraph;
        }
        $openGraph = clone $this;
        $openGraph->title = basename($openGraph->title);
        $breadcrumbs->openGraphObjects[] = $openGraph;
        $breadcrumbs->openGraphObjects[0]->title = '(All photos)';
        $breadcrumbs->render();

        /* Set up tabs */
        $tabs = new Views\TabView;
        if ($photoCount && $folderCount) {
            $tabs->openGraphObjects[0] = clone $this;
            $tabs->openGraphObjects[0]->title = "$photoCount photos";
            $tabs->openGraphObjects[1] = clone $this;
            $tabs->openGraphObjects[1]->title = "$folderCount folders";
            $tabs->render();
        }

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
