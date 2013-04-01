<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>jQuery OpenID Plugin</title>
    <link rel="stylesheet" type="text/css" media="screen" href="openid.css" />
    <!-- jQuery OpenID Plugin 1.1 Copyright 2009 Jarrett Vance http://jvance.com/pages/jQueryOpenIdPlugin.xhtml -->
</head>
<body>
<h2>Login to <em>
<?php
    require '../../../main.inc';
    echo $cameralife->GetPref('sitename')
?>
</em>  using OpenID</h2>
<form class="openid" method="post" action="process.php">
  <div><ul class="providers">
  <li class="openid" title="OpenID"><img src="images/openidW.png" alt="icon" />
  <span><strong>http://{your-openid-url}</strong></span></li>
  <li class="direct" title="Google">
        <img src="images/googleW.png" alt="icon" /><span>https://www.google.com/accounts/o8/id</span></li>
  <li class="direct" title="Yahoo">
        <img src="images/yahooW.png" alt="icon" /><span>http://yahoo.com/</span></li>
  <li class="username" title="AOL screen name">
        <img src="images/aolW.png" alt="icon" /><span>http://openid.aol.com/<strong>username</strong></span></li>
  <li class="username" title="MyOpenID user name">
        <img src="images/myopenid.png" alt="icon" /><span>http://<strong>username</strong>.myopenid.com/</span></li>
  <li class="username" title="Flickr user name">
        <img src="images/flickr.png" alt="icon" /><span>http://flickr.com/<strong>username</strong>/</span></li>
  <li class="username" title="Technorati user name">
        <img src="images/technorati.png" alt="icon" /><span>http://technorati.com/people/technorati/<strong>username</strong>/</span></li>
  <li class="username" title="Wordpress blog name">
        <img src="images/wordpress.png" alt="icon" /><span>http://<strong>username</strong>.wordpress.com</span></li>
  <li class="username" title="Blogger blog name">
        <img src="images/blogger.png" alt="icon" /><span>http://<strong>username</strong>.blogspot.com/</span></li>
  <li class="username" title="LiveJournal blog name">
        <img src="images/livejournal.png" alt="icon" /><span>http://<strong>username</strong>.livejournal.com</span></li>
  <li class="username" title="ClaimID user name">
        <img src="images/claimid.png" alt="icon" /><span>http://claimid.com/<strong>username</strong></span></li>
  <li class="username" title="Vidoop user name">
        <img src="images/vidoop.png" alt="icon" /><span>http://<strong>username</strong>.myvidoop.com/</span></li>
  <li class="username" title="Verisign user name">
        <img src="images/verisign.png" alt="icon" /><span>http://<strong>username</strong>.pip.verisignlabs.com/</span></li>
  </ul></div>
  <fieldset>
  <label for="openid_username">Enter your <span>Provider user name</span></label>
  <div><span></span><input type="text" name="openid_username" /><span></span>
  <input type="submit" value="Login" /></div>
  </fieldset>
  <fieldset>
  <label for="openid_identifier">Enter your <a class="openid_logo" href="http://openid.net">OpenID</a></label>
  <div><input type="text" name="openid_identifier" />
  <input type="submit" value="Login" /></div>
  </fieldset>
</form>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.1/jquery.min.js"></script>
<script type="text/javascript" src="jquery.openid.js"></script>
<script type="text/javascript">  $(function() { $("form.openid:eq(0)").openid(); });</script>
</body>
</html>
