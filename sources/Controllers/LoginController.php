<?php
namespace CameraLife\Controllers;
use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * Displays the Search page
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class LoginController extends HtmlController
{
    public function __construct($id)
    {
        parent::__construct();
        $this->title = 'Login';
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        /* Set up common page parts */
        $this->htmlHeader($cookies);

        try {
            # Mewp told me specifically not to use SERVER_NAME.
            # Change 'localhost' to your domain name.
            $openid = new \LightOpenID($_SERVER['SERVER_NAME']);
            if (!$openid->mode) {
                if (isset($_POST['openid_identifier'])) {
                    $openid->identity = $_POST['openid_identifier'];
                    $openid->required = array('contact/email');
                    $openid->optional = array('namePerson', 'namePerson/friendly');
                    header('Location: ' . $openid->authUrl());
                    return;
                }
            } elseif ($openid->mode == 'cancel') {
                echo 'User has canceled authentication!';
            } else {
                $id = "";
                $email = "";
                if ($openid->validate()) {
                    $id = $openid->identity;
                    $attr = $openid->getAttributes();
                    $email = $attr['contact/email'];
                    if (strlen($email)) {
                        session_start();
                        $_SESSION['openid_identity'] = $openid->identity;
                        $_SESSION['openid_email'] = $attr['contact/email'];
                        Models\User::userWithOpenId($_SESSION['openid_identity'], $_SESSION['openid_email']);
                        header('Location: ' . MainPageController::getUrl());
                        return;
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
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Login</h3>
                </div>
                <div class="panel-body">
                        <p class="lead">Choose an OpenID provider to login:</p>

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
<?php

        /* Render footer */
        $this->htmlFooter();
    }
    
    public function handlePost($get, $post, $files, $cookies)
    {
        try {
            # Mewp told me specifically not to use SERVER_NAME.
            # Change 'localhost' to your domain name.
            $openid = new \LightOpenID($_SERVER['SERVER_NAME']);
            if (!$openid->mode) {
                if (isset($_POST['openid_identifier'])) {
                    $openid->identity = $_POST['openid_identifier'];
                    $openid->required = array('contact/email');
                    $openid->optional = array('namePerson', 'namePerson/friendly');
                    header('Location: ' . $openid->authUrl());
                }
            } elseif ($openid->mode == 'cancel') {
                echo 'User has canceled authentication!';
            } else {
                $id = "";
                $email = "";
                if ($openid->validate()) {
                    $id = $openid->identity;
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
