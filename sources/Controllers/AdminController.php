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

class AdminController extends HtmlController
{
    public $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Models\Statistics;
        $this->title = 'Site administration';
        $this->icon = 'bar-chart';
    }
    
    private function latestAvailableVersion()
    {
        $url = 'https://api.github.com/repos/fulldecent/cameralife/releases';
        $options  = array('http' => array('user_agent'=>'Camera Life'));
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        if ($response === false) {
            return NULL;
        }
        $json = json_decode($response);
        if (isset($json[0]->tag_name)) {
            return $json[0]->tag_name;
        }
        return NULL;
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
            throw new \Exception('You are not authorized to view this page');
        }

        /* Set up the page view */
        $view = new Views\AdminView;
        $view->cameraLifeRunningVersion = constant('CAMERALIFE_VERSION');
        $view->cameraLifeLatestVersion = $this->latestAvailableVersion();
        $checkpointLogs = intval(Models\Preferences::valueForModuleWithKey('CameraLife', 'checkpointlogs'));
        $view->numLogsSinceCheckpoint = Models\Database::selectOne('logs', 'COUNT(*)', 'id>' . $checkpointLogs);
        $checkpointComments = intval(Models\Preferences::valueForModuleWithKey('CameraLife', 'checkpointcomments'));
        $view->numCommentsSinceCheckpoint = Models\Database::selectOne('comments', 'COUNT(*)', 'id>' . $checkpointComments);
        $view->numNewUsers = Models\Database::selectOne('users', 'COUNT(*)', 'auth=1');
        $view->numFlagged = Models\Database::selectOne('photos', 'COUNT(*)', 'status=1');

        $view->appearanceUrl = AdminAppearanceController::getUrl();
        $view->logsUrl = AdminLogsController::getUrl();
        $view->commentsUrl = AdminCommentsController::getUrl();
        $view->fileStoreUrl = AdminFileStoreController::getUrl();
        $view->securityUrl = AdminSecurityController::getUrl();
        $view->thumbnailUrl = AdminThumbnailController::getUrl();

        $preferences = array();
        $preferences[] = ['module' => 'CameraLife', 'key' => 'sitename', 'type' => 'string', 'name' => 'Site name'];
        $preferences[] = ['module' => 'CameraLife', 'key' => 'sitename', 'type' => 'string', 'name' => 'Site abbreviation'];
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
}
