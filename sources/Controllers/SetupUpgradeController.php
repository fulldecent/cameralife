<?php
namespace CameraLife\Controllers;

use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Upgrades the database schema version
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2015 William Entriken
 * @access public
 */

class SetupUpgradeController extends HtmlController
{
    public $model;

    public static function getUrl()
    {
        return constant('BASE_URL') . '/index.php?page=setupUpgrade';
    }

    // cannot use parent because database is not accessible
    public function __construct($modelId = null)
    {
        $this->siteName = null;
        $this->title = $this->siteName;
        $this->type = 'website';
        $this->image = constant('BASE_URL') . '/assets/main.png';
        $this->imageType = 'image/png';
        $this->url = self::getUrl();
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        $nextInstaller = null;
        if (!Models\Database::installedSchemaIsCorrectVersion()) {
            $driver = Models\Database::driverName();
            $class = 'SchemaUpdater' . ucwords($driver) . Models\Database::installedSchemaVersion();
            $nextInstaller = 'CameraLife\\Models\\' . $class;
        }
        ?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <title>Install Camera Life</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="//netdna.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
            <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
            <!-- CAMERALIFE PHONE HOME Global site tag (gtag.js) - Google Analytics -->
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('event', 'sign_up', {'checkout_step': 3});
              gtag('config', 'UA-52764-13');
            </script>
        </head>

        <body>
            <div class="jumbotron">
                <div class="container">
                    <h2>
                        <i class="fa fa-camera-retro"></i>
                        You are updating Camera Life version <?= constant('CAMERALIFE_VERSION') ?>
                    </h2>
                    <p>
                        <a target="_blank" href="http://fulldecent.github.io/cameralife">
                            <i class="glyphicon glyphicon-home"></i>
                            Camera Life project page</a>
                        <a target="_blank" href="mailto:cameralifesupport@phor.net">
                            <i class="glyphicon glyphicon-envelope"></i>
                            Email support</a>
                    </p>

                </div>
            </div>

        <div class="container">
            <h1>Installed database: <?= Models\Database::driverName() ?></h1>
            <h1>Installed schema version: <?= Models\Database::installedSchemaVersion() ?></h1>
            <h1>Needed schema version: <?= Models\Database::requiredSchemaVersion() ?></h1>
        <?php
            if ($nextInstaller) {
                echo "<h1>Next updater: ".$nextInstaller."</h1><hr>";
                $upgrader = new $nextInstaller;
                echo '<blockquote class="lead">' . $upgrader->scriptInfo . '</blockquote>';
                $canUpgrade = $upgrader->canUpgrade();
                if ($canUpgrade === true) {
                    $result = $upgrader->doUpgrade();
                    if (true === $result) {
                        echo '<p class="lead text-success">Upgrade complete ';
                        if (Models\Database::driverName() == 'mysql') {
                          echo 'Please update your <code>modules/config.inc</code> and add ';
                          echo '<code>$db_schema_version = ' . (Models\Database::$schemaVersion + 1) . ';</code> ';
                        }
                        echo '<a href="../../" class="btn btn-primary btn-large">Continue</a><p>';
                    } else {
                        echo '<p class="text-success">Upgrade failed</p>';
                    }
                } else {
                    echo '<p class="lead text-danger">Automatic upgrade not possible.</p>';
                    echo '<blockquote class="lead">' . $canUpgrade . '</blockquote>';
                }


            } else {
                echo "<h1>No upgrade needed</h1>";
            }
        ?>
        </div>

        </body>
        </html>

<?php
    }

}
