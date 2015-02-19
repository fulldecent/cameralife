<?php
namespace CameraLife\Controllers;

use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * The install page for new users
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */

class SetupInstallController extends HtmlController
{
    public $model;

    public static function getUrl()
    {
        return constant('BASE_URL') . '/index.php?page=setupInstall';
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
                        header('Location: ' . SetupInstall2Controller::getUrl());
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

        if (file_exists('../../config.php')) {
            throw new \Exception("Camera Life already appears to be set up, because modules/config.inc exists.");
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
                        You are installing Camera Life version <?= constant('CAMERALIFE_VERSION') ?>
                    </h2>
                    <p>To upgrade instead, copy in your old <var>config.php</var> file</p>
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
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Login</h3>
                </div>
                <div class="panel-body">
                        <p class="lead">To begin, login with an OpenID provider:</p>

                        <form class="form-inline" method="post">
                            <input type="hidden" name="action" value="verify"/>
                            <button class="btn btn-primary"  name="openid_identifier" value="https://www.google.com/accounts/o8/id"><i class="fa fa-google"></i> Google</button>
                            <button class="btn btn-primary"  name="openid_identifier" value="http://me.yahoo.com/"><i class="fa fa-yahoo"></i> Yahoo</button>
                        </form>

                        <hr>

                        <form class="form-inline" method="post">
                            <input type="hidden" name="action" value="verify"/>
                            Other OpenID
                            <input name="openid_identifier" class="form-control" value="http://"/>
                            <input class="btn btn-primary" type="submit" value="Login"/>
                        </form>
                </div>
            </div>

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
                        header('Location: http://indexn2.php');
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
