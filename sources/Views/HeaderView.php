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
            <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
            <link rel="search" href="<?= htmlspecialchars($this->openSearchUrl) ?>" type="application/opensearchdescription+xml"
                  title="Content Search"/>
            <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
            <link href="<?= constant('BASE_URL') ?>/assets/main.css" rel="stylesheet">

            <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
            <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
            <!--[if lt IE 9]>
              <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
              <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->
        </head>
        <nav class="navbar navbar-default navbar-static-top" role="navigation">
            <div class="container">
                <a class="navbar-brand" href="<?= constant('BASE_URL') ?>/"><?= htmlspecialchars($this->openGraphObject->siteName) ?></a>
                <ul class="nav navbar-nav navbar-right">
                    <li class="<?= get_class($this->openGraphObject) == 'CameraLife\Controllers\FavoritesController' ? 'active' : '' ?>"><a href="<?= htmlspecialchars($this->favoritesUrl) ?>"><i class="fa fa-star" style="color:gold"></i> My favorites (<?= $this->numFavorites ?>)</a></li>
                    <?php
                    if ($this->currentUser->isLoggedIn) {
                        ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><img
                                    src="<?= $gravitarHTML ?>" height=16
                                    width=16> <?= htmlspecialchars($this->currentUser->name) ?> <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                        <?php
                        if ($this->currentUser->authorizationLevel >= 5) {
                            ?>
                                <li><a href="<?= htmlspecialchars($this->adminUrl) ?>">Administer</a></li>
                            <?php
                        }
                        ?>

                                <li class="divider"></li>
                                <li><a href="<?= htmlspecialchars($this->logoutUrl) ?>">Sign Out</a></li>
                            </ul>
                        </li>
                    <?php
                    } else {
?>
                        <li><a class=""
                           href="<?= htmlspecialchars($this->loginUrl) ?>">
                            <i class="fa fa-facebook"></i>
                            <i class="fa fa-google-plus"></i>
                            <i class="fa fa-twitter"></i>
                            Login / Free account
                        </a>
                        </li>
                    <?php
                    } ?>
                </ul>
                <form class="navbar-form navbar-right" action="<?= htmlspecialchars($this->searchUrl) ?>" method="get">
                    <input type="text" class="form-control" placeholder="Search" name="id"/>
                </form>
            </div>
        </nav>
        <div class="container">
    <?php
    }

    public function htmlFooter()
    {
        $statsURL = Controllers\StatisticsController::getUrl();

        ?>
        <hr>

        <footer>
            <p>
                <a href="mailto:<?= htmlspecialchars($this->ownerEmail) ?>"><i class="fa fa-envelope"></i> Contact site
                    owner</a>
                &nbsp;
                <a href="<?= htmlspecialchars($statsURL) ?>"><i class="fa fa-signal"></i> Site stats</a>
                &nbsp;
                <a href="http://fulldecent.github.io/cameralife"><i class="fa fa-globe"></i> Built with Camera Life</a>
            </p>
        </footer>

        </div>

        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

        <?php
        if (!empty($analyticsID)) {
            ?>
            <!--TRACKING CODE-->
            <script type="text/javascript">
                var _gaq = _gaq || [];
                _gaq.push(['_setAccount', '<?= $analyticsID ?>']);
                _gaq.push(['_trackPageview']);

                (function () {
                    var ga = document.createElement('script');
                    ga.type = 'text/javascript';
                    ga.async = true;
                    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(ga, s);
                })();
            </script>
            <!--END TRACKING CODE-->
        <?php
        }
        ?>
        </body>
        </html>
    <?php
    }
}

