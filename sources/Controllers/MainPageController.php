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
        switch($view->activeSection) {
            case 'newest-folders':
                $search->sort = 'newest';
                foreach($search->getFolders() as $folder) {
                    $view->openGraphsForTop[] = new FolderController($folder->id);
                }
                break;
            case 'rand':
            case 'popular':
            case 'unpopular':
            case 'newest':
                $search->sort = $view->activeSection;
                foreach($search->getPhotos() as $photo) {
                    $view->openGraphsForTop[] = new PhotoController($photo->id);
                }
                break;
            default:
                $search->sort = 'rand';
                foreach($search->getPhotos() as $photo) {
                    $view->openGraphsForTop[] = new PhotoController($photo->id);
                }
        }

        $root = Models\Folder::getRootFolder();
        $root->sort = 'newest';
        $root->setPage(0, 6);
        $rootFolders = array();
        foreach ($root->getDescendants() as $descendant) {
            $rootFolders[] = new FolderController($descendant->id);
        }
        $view->folders = $rootFolders;
        
        $view->rootOpenGraph = new FolderController('/');

        $view->tagCollections = Models\TagCollection::getCollections();
        $view->render();

        $this->htmlFooter();
    }
}
