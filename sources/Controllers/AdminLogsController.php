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

class AdminLogsController extends HtmlController
{
    public $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Models\Statistics;
        $this->title = 'Log viewer';
        $this->icon = 'list';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
            throw new \Exception('You are not authorized to view this page');
        }

        /* Set up the page view */
        $view = new Views\AdminLogsView;
        $view->checkpointId = intval(Models\Preferences::valueForModuleWithKey('CameraLife', 'checkpointlogs'));
        $view->checkpointDate = Models\Database::selectOne('logs', 'max(user_date)', 'id=' . $view->checkpointId);
        $view->showFromMe = isset($get['fromMe']) && $get['fromMe'];
        $view->showFromRegistered = isset($get['fromRegistered']) && $get['fromRegistered'];
        $view->showFromUnregistered = isset($get['fromUnregistered']) && $get['fromUnregistered'];
        $view->showChangedPhotos = isset($get['changedPhotos']) && $get['changedPhotos'];
        $view->showChangedTags = isset($get['changedTags']) && $get['changedTags'];
        $view->showChangedUsers = isset($get['changedUsers']) && $get['changedUsers'];
        $view->showChangedPrefs = isset($get['changedPreferences']) && $get['changedPreferences'];

        if (!$view->showFromMe && !$view->showFromRegistered && !$view->showFromUnregistered) {
            $view->showFromMe = true;
            $view->showFromRegistered = true;
            $view->showFromUnregistered = true;
        }

        if (!$view->showChangedPhotos && !$view->showChangedTags && !$view->showChangedUsers && !$view->showChangedPrefs) {
            $view->showChangedPhotos = true;
            $view->showChangedTags = true;
            $view->showChangedUsers = true;
            $view->showChangedPrefs = true;
        }

        /* Query the audit logs */
        $currentUser = Models\User::currentUser($cookies);

        $condition = "(0 ";
        $condition .= $view->showChangedPhotos ? "OR record_type = 'photo' " : '';
        $condition .= $view->showChangedTags ? "OR record_type = 'album' " : '';
        $condition .= $view->showChangedUsers ? "OR record_type = 'user' " : '';
        $condition .= $view->showChangedPrefs ? "OR record_type = 'preference' " : '';

        $condition .= ") AND (0 ";
        $condition .= $view->showFromMe ? "OR user_name = '" . $currentUser->name . "' " : '';
        $condition .= $view->showFromRegistered ? "OR (user_name LIKE '_%' AND user_name != '" . $currentUser->name . "')" : '';
        $condition .= $view->showFromUnregistered ? "OR user_name = '' " : '';
        $condition .= ") ";

        $condition .= " AND logs.id > " . ($view->checkpointId);
        $extra = "GROUP BY record_id, record_type, value_field ORDER BY logs.id DESC";

        $query = Models\Database::select(
            'logs',
            'record_type, record_id, value_field, MAX(logs.id) as maxid',
            $condition,
            $extra
        );
        $auditTrails = array();
        while ($record = $query->fetchAssoc()) {
            $auditTrails[] = Models\AuditTrail::getAuditTrailWithID($record['maxid']);
        }
        $view->auditTrails = $auditTrails;


        $this->htmlHeader($cookies);
        $view->render();
        $this->htmlFooter();
    }
}
