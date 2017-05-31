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
        if (Models\Database::$schemaVersion != Models\Database::REQUIRED_SCHEMA_VERSION) {
            $nextInstaller = "CameraLife\Models\SchemaUpdater" . (Models\Database::$schemaVersion + 1);
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
            <script>
              (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
              (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
              m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
              })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
              ga('create', 'UA-52764-13', 'auto');
              ga('send', 'pageview');
              ga('send', 'event', 'install', 'step', 'step 1');            
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
            <h1>Installed database version: <?= Models\Database::$schemaVersion ?></h1>
            <h1>Latest database version: <?= Models\Database::REQUIRED_SCHEMA_VERSION ?></h1>
        <?php
            if ($nextInstaller) {
                echo "<h1>Next updater: ".$nextInstaller."</h1><hr>";
                $upgrader = new $nextInstaller;
                echo '<blockquote class="lead">' . $upgrader->scriptInfo . '</blockquote>';
                $canUpgrade = $upgrader->canUpgrade();
                if ($canUpgrade === true) {
                    $result = $upgrader->doUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix);
                    if (true === $result) {
                        echo '<p class="lead text-success">Upgrade complete ';
                        echo 'Please update your <code>modules/config.inc</code> and add ';
                        echo '<code>$db_schema_version = ' . (Models\Database::$schemaVersion + 1) . ';</code> ';
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

    public function handlePost($get, $post, $files, $cookies)
    {
        try {
            // Mewp told me specifically not to use SERVER_NAME.
            // Change 'localhost' to your domain name.
            $openid = new \LightOpenID($_SERVER['SERVER_NAME']);
            if (!$openid->mode) {
                if (isset($post['openid_identifier'])) {
                    $openid->identity = $post['openid_identifier'];
                    $openid->required = array('contact/email');
                    $openid->optional = array('namePerson', 'namePerson/friendly');
                    header('Location: ' . $openid->authUrl());
                }
            } elseif ($openid->mode == 'cancel') {
                echo 'User has canceled authentication!';
            } else {
                $identity = "";
                $email = "";
                if ($openid->validate()) {
                    $identity = $openid->identity;
                    $attr = $openid->getAttributes();
                    $email = $attr['contact/email'];
                    if (strlen($email)) {
                        session_start();
                        $_SESSION['openid_identity'] = $openid->identity;
                        $_SESSION['openid_email'] = $attr['contact/email'];
                        header('Location: index.php');
                    } else {
                        throw new \Exception('Enough detail (email address) was not provided to process your login.');
                    }
                } else {
                    throw new \Exception('Provider did not validate your login');
                }
            }
        } catch (\ErrorException $e) {
            echo $e->getMessage();
        }
    }
}
