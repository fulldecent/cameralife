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
class SearchController extends HtmlController
{
    private $model;

    public function __construct($id)
    {
        parent::__construct();
        $this->model = new Models\Search($id);
        $this->title = $this->model->query;
        $this->icon = 'search';
        $this->url = self::getUrlForID($id);
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        $photoCount = $this->model->getPhotoCount();
        $folderCount = $this->model->getFolderCount();
        $albumCount = $this->model->getTagCount();

        // Sometimes we're sure an album page is relevant - redirect there
        if (!$folderCount && $albumCount == 1) {
            /* TODO
            $count_term = $cameralife->database->SelectOne('albums', 'COUNT(*)', "term LIKE '" . $get['q'] . "'");
            if ($count_term == 1) {
              $albumid = $cameralife->database->SelectOne('albums', 'id', "term LIKE '" . $get['q'] . "'");
              header('Location: ' . $cameralife->baseURL . '/album.php?id=' . $albumid);
              echo 'redirecting... ' . $cameralife->baseURL . '/album.php?id=' . $albumid;
              exit(0);
            }
            */
        }

        // Sometimes we're sure a folder page is relevant - redirect there
        if (!$albumCount && !$photoCount && $folderCount == 1) {
            /* TODO
            list($folder) = $search->getFolders();
            $folderOpenGraph = $folder->GetOpenGraph();
            header('Location: ' . $folderOpenGraph['op:url']);
            exit(0);
            */
        }


        $start = isset($get['start']) ? $get['start'] : 0;
        $this->model->setPage($start);

        /* Set up common page parts */
        $this->htmlHeader($cookies);

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
        $start = isset($get['start']) ? $get['start'] : 0;
        $objects = array();
        foreach($this->model->getPhotos() as $photo) {
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
