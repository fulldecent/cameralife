<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;
use CameraLife\Controllers as Controllers;

/**
 * Theme name - Bootstrap
 * @author William Entriken<cameralife@phor.net>
 * @access public
 * @copyright 2014 William Entriken
 * @todo make this HTML valid
 */
class HeaderView extends View
{
    /**
     * openGraphObject
     *
     * @var    Models\OpenGraphObject
     * @access public
     */
    public $openGraphObject;

    /**
     * currentUser
     *
     * @var    Models\User
     * @access public
     */
    public $currentUser;

    public $openSearchUrl;
    public $searchUrl;
    public $adminUrl;
    public $ownerEmail;
    public $logoutUrl;
    public $loginUrl;
    public $skipNav;

    public $favoritesUrl;

    public $numFavorites = 0;

    /**
     * Generate partial output for HTML header
     *
     * @access public
     * @static
     * @return void
     */
    public function render()
    {
          $gravitarHTML = htmlentities($this->currentUser->gravitarUrl());
        ?>
        <!doctype html>
        <html lang="en">
          <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <title><?= htmlspecialchars($this->openGraphObject->title) ?></title>
            <?php
                $this->openGraphObject->htmlRenderMetaTags();
            ?>
            <meta name="generator" content="Camera Life version <?= constant('CAMERALIFE_VERSION') ?>">
            <meta name="author" content="<?= htmlspecialchars($this->ownerEmail) ?>">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
            <link rel="search" href="<?= htmlspecialchars($this->openSearchUrl) ?>" type="application/opensearchdescription+xml" title="Content Search"/>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
            <link rel="stylesheet" href="<?= constant('BASE_URL') ?>/assets/main.css">
        </head>
    <?php
    }
}
