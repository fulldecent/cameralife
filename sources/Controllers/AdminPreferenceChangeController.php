<?php
namespace CameraLife\Controllers;

use CameraLife\Models as Models;

/**
 * Displays the Admin Appearance page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */

class AdminPreferenceChangeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->title = 'Preference Change';
        $this->icon = 'list';
    }

    // Uses:
    //    $post['target'] is a redirect target
    //    other $post['module|param'] = $value are executed as given
    //
    public function handlePost($get, $post, $files, $cookies)
    {
        if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
            throw new \Exception('You are not authorized to view this page');
        }
        if (!isset($post['target'])) {
            throw new \Exception('No target specified');
        }
        
        foreach ($post as $key => $val) {
            if ($key == 'target') {
                continue;
            } else {
                $array = explode('|', $key);
                if (count($array) != 2) {
                    throw new \Exception('Invalid module / key');
                }
                Models\Preferences::setValueForModuleWithKey($val, $array[0], $array[1]);
            }
        }
        
        header("Location: " . htmlspecialchars($post['target']));
    }
}
