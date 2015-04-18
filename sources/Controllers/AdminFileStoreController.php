<?php
namespace CameraLife\Controllers;

use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Displays the Admin File Store page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */

class AdminFileStoreController extends HtmlController
{
    public $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Models\Statistics;
        $this->title = 'Site stats';
        $this->icon = 'bar-chart';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
            throw new \Exception('You are not authorized to view this page');
        }

        /* Set up the page view */
        $view = new Views\AdminPreferencesView;
        $view->moduleName = 'File Store';

        $preferences = array();
        $preferences[] = ['module' => 'LocalFileStore', 'key' => 'photo_dir', 'type' => 'directory', 'name' => 'Photo directory'];
        $preferences[] = ['module' => 'LocalFileStore', 'key' => 'cache_dir', 'type' => 'directoryrw', 'name' => 'Cache directory'];
        $view->preferences = $preferences;

        $this->htmlHeader($cookies);
        $view->render();
        $this->htmlFooter();
    }
    
    public function handlePost($get, $post, $files, $cookies)
    {
        if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
            throw new \Exception('You are not authorized to view this page');
        }
      
        $prefs = array();
      
        foreach ($post as $key => $val) {
            if ($key == 'target') {
                continue;
            } else {
                $array = explode('|', $key);
                if (count($array) != 2) {
                    $cameralife->error('Invalid module / key');
                }
                $prefs[] = array('module' => $array[0], 'param' => $array[1], 'value' => $val);
            }
        }
        
        foreach ($prefs as $pref) {
            if (isset($pref['module']) && isset($pref['param']) && isset($pref['value'])) {
                Models\Preferences::setValueForModuleWithKey($pref['value'], $pref['module'], $pref['param']);
            } else {
                var_dump($prefs);
                die ('passed wrong');
            }
        }
        echo "UPDATE DONE";
    }    
}
