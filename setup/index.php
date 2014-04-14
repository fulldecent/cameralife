<?php
/**
 * Displays post installation notifcation messages
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2014 William Entriken
 * @access public
 */

$continue = true;
if (file_exists('../modules/config.inc')) {
    die("Camera Life already appears to be set up, because modules/config.inc exists.");
}

require '../modules/security/openid/lightopenid/openid.php';
try {
    # Mewp told me specifically not to use SERVER_NAME.
    # Change 'localhost' to your domain name.
    $openid = new LightOpenID($_SERVER['SERVER_NAME']);
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
                header('Location: index2.php');
            } else {
                die ('Enough detail (email address) was not provided to process your login.');
            }
        } else {
            die ('Provider did not validate your login');
        }
    }
} catch (ErrorException $e) {
    echo $e->getMessage();
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Install Camera Life</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="../modules/security/openid/openid.css" rel="stylesheet">
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-52764-13']);
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
</head>

<body>
<nav class="navbar navbar-default" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <span class="navbar-brand">INSTALL CAMERA LIFE</span>
        </div>
        <ul class="nav navbar-nav">
            <li class="active"><a>1. Login</a></li>
            <li><a>2. Setup</a></li>
            <li><a>3. Use Camera Life</a></li>
        </ul>
        <a class="btn btn-default navbar-btn pull-right" href="mailto:cameralifesupport@phor.net">
            <i class="glyphicon glyphicon-envelope"></i>
            Email support
        </a>
        <a class="btn btn-default navbar-btn pull-right" href="http://fulldecent.github.com/cameralife">
            <i class="glyphicon glyphicon-home"></i>
            Camera Life project page
        </a>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-lg-6 col-lg-offset-3">
            <div class="well">
                <h2>Welcome to Camera Life</h2>
                <p class="alert alert-info">You are now installing version <?= trim(file_get_contents('../VERSION')) ?>. To upgrade instead, read <a href="../UPGRADE">UPGADE</a>.</p>
                <p class="lead">To begin, login with an OpenID provider:</p>
              	<form class="form-inline" method="post" id="openid_form">
              		<input type="hidden" name="action" value="verify" />
              		<fieldset>
              			<div id="openid_choice">
              				<div id="openid_btns"></div>
              			</div>
              			<div id="openid_input_area">
              				<input id="openid_identifier" name="openid_identifier" type="text" value="http://" />
              				<input class="btn btn-primary" id="openid_submit" type="submit" value="Sign-In"/>
              			</div>
              			<noscript>
              				<p>OpenID is service that allows you to log-on to many different websites using a single identity.
              				Find out <a href="http://openid.net/what/">more about OpenID</a> and <a href="http://openid.net/get/">how to get an OpenID enabled account</a>.</p>
              			</noscript>
              		</fieldset>
              	</form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript" src="../modules/security/openid/openid-jquery.js"></script>
<script type="text/javascript" src="../modules/security/openid/openid-en.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		openid.init('openid_identifier');
	});
</script>

</body>
</html>
