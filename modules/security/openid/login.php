<?php
$features = array('theme');
require '../../../main.inc';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Install Camera Life</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="openid.css" rel="stylesheet">
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', '<?= $cameralife->theme->getPref('analytics') ?>']);
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

<div class="container">
    <h1><?php echo $cameralife->getPref('sitename') ?></h1>
    <hr>
    <h2>Login with OpenID
        <small>using any provider below</small>
    </h2>
  	<form class="form-inline" action="process.php" method="post" id="openid_form">
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
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  	<script type="text/javascript" src="openid-jquery.js"></script>
  	<script type="text/javascript" src="openid-en.js"></script>
  	<script type="text/javascript">
  		$(document).ready(function() {
  			openid.init('openid_identifier');
  		});
  	</script>
</body>
</html>
