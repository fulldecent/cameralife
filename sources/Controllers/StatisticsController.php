<?php
namespace CameraLife\Controllers;
use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Displays the Stats page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */

class StatisticsController extends HtmlController
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
        /* Set up the page view */
        $view = new Views\StatisticsView;
        $view->statistics = $this->model;

        $this->htmlHeader($cookies);
        $view->render();
        $this->htmlFooter();
    }
}
