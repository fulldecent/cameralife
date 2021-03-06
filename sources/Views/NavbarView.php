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
class NavbarView extends View
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

        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-3" style="background:rgba(247,249,249,0.90)">
          <div class="container">
            <a class="navbar-brand" href="<?= constant('BASE_URL') ?>/"><?= htmlspecialchars($this->openGraphObject->siteName) ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a href="<?= htmlspecialchars($this->favoritesUrl) ?>" class="nav-link"><i class="fa fa-star" style="color:gold"></i> My favorites (<?= $this->numFavorites ?>)</a>
                </li>
                <?php if ($this->currentUser->isLoggedIn) { ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="<?= $gravitarHTML ?>" height=16 width=16> <?= htmlspecialchars($this->currentUser->name) ?>
                  </a>
                  <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <?php if ($this->currentUser->authorizationLevel >= 5) { ?>
                    <a class="dropdown-item" href="<?= htmlspecialchars($this->adminUrl) ?>">Administer</a>
                    <?php } ?>
                    <a class="dropdown-item" href="<?= htmlspecialchars($this->logoutUrl) ?>">Sign Out</a>
                  </div>
                </li>
                <?php } else { ?>
                <li class="nav-item">
                    <a href="<?= htmlspecialchars($this->loginUrl) ?>" class="nav-link">Login / Free account</a>
                </li>
                <?php } ?>
              </ul>
              <form class="form-inline my-2 my-lg-0" action="<?= htmlspecialchars($this->searchUrl) ?>" method="get">
                <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" name="id">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
              </form>
            </div>
          </div>
        </nav>

    <?php
    }
}
