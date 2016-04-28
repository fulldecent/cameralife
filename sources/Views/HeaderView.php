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
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <title><?= htmlspecialchars($this->openGraphObject->title) ?></title>
            <?php
                $this->openGraphObject->htmlRenderMetaTags();
            ?>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="generator" content="Camera Life version <?= constant('CAMERALIFE_VERSION') ?>">
            <meta name="author" content="<?= htmlspecialchars($this->ownerEmail) ?>">
            <!-- Le styles -->
            <link rel="stylesheet" href="//cdn.rawgit.com/twbs/bootstrap/v4-dev/dist/css/bootstrap.css" crossorigin="anonymous">
            <link rel="search" href="<?= htmlspecialchars($this->openSearchUrl) ?>" type="application/opensearchdescription+xml"
                  title="Content Search"/>
            <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
            <link rel="stylesheet" href="<?= constant('BASE_URL') ?>/assets/main.css">

            <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
            <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
            <!--[if lt IE 9]>
              <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
              <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->
        </head>
    <?php
    }
}
