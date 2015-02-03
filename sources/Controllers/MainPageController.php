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

class MainPageController extends HtmlController
{
    public static function getUrl()
    {
        return constant('BASE_URL') . '/';
    }
    
    public function handleGet($get, $post, $files, $cookies)
    {
        $this->htmlHeader($cookies);

        /* Set up the page view */
        $view = new Views\MainPageView;
        $view->adminUrl = AdminController::getUrl();

        $search = new Models\Search;
        $search->setPage(0, 11);
        $search->sort = 'rand';

        $view->openGraphsForTop = array();

        $view->activeSection = isset($get['section']) ? $get['section'] : 'rand';
        switch ($view->activeSection) {
            case 'newest-folders':
                $search->sort = 'newest';
                foreach ($search->getFolders() as $folder) {
                    $view->openGraphsForTop[] = new FolderController($folder->id);
                }
                break;
            case 'rand':
            case 'popular':
            case 'unpopular':
            case 'newest':
                $search->sort = $view->activeSection;
                foreach ($search->getPhotos() as $photo) {
                    $view->openGraphsForTop[] = new PhotoController($photo->id);
                }
                break;
            default:
                $search->sort = 'rand';
                foreach ($search->getPhotos() as $photo) {
                    $view->openGraphsForTop[] = new PhotoController($photo->id);
                }
        }

        $root = Models\Folder::getRootFolder();
        $root->sort = 'newest';
        $root->setPage(0, 6);
        
        $view->folderAndPhotoOGs = array();
        foreach ($root->getDescendants() as $descendant) {
            $folderController = new FolderController($descendant->id);
            $folderAndPhotoOG = array($folderController);
            $photoOpenGraphs = array();
            $descendant->sort = 'rand';
            $descendant->setPage(0, 11);
            foreach ($descendant->getPhotos() as $photo) {
                $photoController = new PhotoController($photo->id);
                $photoOpenGraphs[] = $photoController;
            }
            $folderAndPhotoOG[] = $photoOpenGraphs;
            $view->folderAndPhotoOGs[] = $folderAndPhotoOG;
        }
        
        $view->rootOpenGraph = new FolderController('/');

        $view->tagCollections = Models\TagCollection::getCollections();
        $view->render();

        $this->htmlFooter();
    }
}
