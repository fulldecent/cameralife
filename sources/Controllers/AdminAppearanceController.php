<?php
namespace CameraLife\Controllers;

use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Displays the Admin Appearance page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */

class AdminAppearanceController extends HtmlController
{
    public function __construct()
    {
        parent::__construct();
        $this->title = 'Admin Appearance';
        $this->icon = 'bar-chart';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
            throw new \Exception('You are not authorized to view this page');
        }

        /* Set up the page view */
        $view = new Views\AdminPreferencesView;
        $view->moduleName = 'Site Appearance';

        $preferences = array();
        $preferences[] = ['module' => 'CameraLife', 'key' => 'sitename', 'type' => 'string', 'name' => 'Site name'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'owner_email', 'type' => 'string', 'name' => 'Owner email address'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'rewrite', 'type' => 'yesno', 'name' => 'Use pretty URLs'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'autorotate', 'type' => 'yesno', 'name' => 'Autorotate photos'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'thumbsize', 'type' => 'number', 'name' => 'Size for thumbnails'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'scaledsize', 'type' => 'number', 'name' => 'Size for preview images'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'optionsizes', 'type' => 'string', 'name' => 'Other available sizes', 'help' => 'comma separated (you can also leave this blank)'];
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
