<?php
/**
 * Displays post installation notifcation messages
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Install Camera Life</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-52764-13']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
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
          <li><a>1. Setup</a></li>
          <li class="active"><a>2. Use Camera Life</a></li>
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

    <div class="jumbotron">

      <div class="container">
<?php
if (!isset($_POST['sitepasswd'])) {
?>
        <h2>Promote your account to admin</h2>
        <p>This works when there is exactly ONE user in the system</p>
        <form class="form-inline" role="form" method="POST">
          <div class="form-group">
            <label class="sr-only" for="sitepassword">Site password</label>
            <input type="password" class="form-control" id="sitepassword" placeholder="Site password" name="sitepasswd">
          </div>
          <button type="submit" class="btn btn-default">Promote account</button>
        </form>
<?php 
} else {
  $features=array('security', 'photostore', 'theme');
  require '../main.inc';
  $count = $cameralife->Database->SelectOne('users','count(*)');
  $pass = $cameralife->Database->SelectOne('users','password','username="admin"');
  $error = '';
  if ($count == 1) $error = 'You have not set up a user account to promote';
  if ($count > 2) $error = 'This tool only works for the first user on the system';
  if ($pass != crypt($_POST['sitepasswd'],'admin')) $error = 'Site password not correct';

  if ($error) {
?>
          <p>Error: <?php echo $error ?></p>
<?php
  } else {
    echo "<p>Granding admin...</p>";
    $cameralife->Database->Update('users', array('auth'=>5));
    echo "<p class='text-success'>done</p>";
?>
<?php
  }
}
?>

      </div>
    </div>    
  </body>
</html>
