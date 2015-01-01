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

class AdminCommentsController extends HtmlController
{
    public $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Models\Statistics;
        $this->title = 'Comments viewer';
        $this->icon = 'list';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
            throw new \Exception('You are not authorized to view this page');
        }

        /* Set up the page view */
        $view = new Views\AdminCommentsView;
        $view->checkpointId = intval(Models\Preferences::valueForModuleWithKey('CameraLife', 'checkpointcomments'));
        $view->checkpointDate = Models\Database::selectOne('logs', 'max(user_date)', 'id=' . $view->checkpointId);
        $view->showFromMe = isset($get['fromMe']) && $get['fromMe'];
        $view->showFromRegistered = isset($get['fromRegistered']) && $get['fromRegistered'];
        $view->showFromUnregistered = isset($get['fromUnregistered']) && $get['fromUnregistered'];

        if (!$view->showFromMe && !$view->showFromRegistered && !$view->showFromUnregistered) {
            $view->showFromMe = true;
            $view->showFromRegistered = true;
            $view->showFromUnregistered = true;
        }

        /* Query the comment logs */
        $currentUser = Models\User::currentUser($cookies);

        $condition = "(0 ";
        $condition .= $view->showFromMe ? "OR username = '" . $currentUser->name . "' " : '';
        $condition .= $view->showFromRegistered ? "OR (username LIKE '_%' AND username != '" . $currentUser->name . "')" : '';
        $condition .= $view->showFromUnregistered ? "OR username = '' " : '';
        $condition .= ") ";

        $condition .= " AND id > " . ($view->checkpointId);

        $query = Models\Database::select(
            'comments',
            '*',
            $condition
        );
        $commentRecords = array();
        while ($record = $query->fetchAssoc()) {
            $commentRecords[] = $record;
        }
        $view->commentRecords = $commentRecords;


        $this->htmlHeader($cookies);
        $view->render();
        $this->htmlFooter();
    }
}
