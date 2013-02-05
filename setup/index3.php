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

    <!-- Le styles -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <style type="text/css">
    </style>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    
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

    <div class="navbar">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand">INSTALL CAMERA LIFE</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li><a>1. Prerequisites</a></li>
              <li><a>2. Database</a></li>
              <li class="active"><a>3. Use Camera Life</a></li>
            </ul>
            <a class="btn pull-right" href="mailto:cameralifesupport@phor.net">
              <i class="icon-envelope"></i>
              Email support
            </a>
            <a class="btn pull-right" href="http://fulldecent.github.com/cameralife">
              <i class="icon-home"></i>
              Camera Life homepage
            </a>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">

<!--      <p style="text-align:center"><img src="images/intro1.png"><p> -->
      
      <div class="well">
        <h2>You are running Camera Life <?php readfile('../VERSION') ?></h2>
        <p>Thank you for choosing to install Camera Life. We hope you will find this software is easy to use and fun.
      </div>

      <div class="row">
        <div class="span6">
          <h2>Get your photos ready</h2>
          <p>Collect some photos that you would like to use with the site. Now, either:</p>
          <ul>
            <li>Place them in the folder <code>images/photos</code>, or
            <li>Make your existing folder writable and change your photo directory in the
                <a href="../admin/photostore.php" target="_blank">Photo Storage setup</a>
            <li>Use the <a href="../upload.php" target="_blank">Upload Page</a></li>
            <li>Use <a href="http://zwily.com/iphoto/" target="_blank">iPhotoToGallery</a>, <a href="http://www.digikam.org/" target="_blank">digKkam</a>, <a href="http://gallery.menalto.com/wiki/Gallery_Remote" target="_blank">Gallery Remote</a> or any <a href="http://codex.gallery2.org/Other_Clients#Photonator_.28Mac_OS_X.29" target="_blank">other software</a> compatible with the Gallery Remote API</li>
            <li>Use rsync to photos from your desktop to your server</li>
            <li>Advanced setup: change your <a href="../admin/photostore.php" target="_blank">Photo Storage setup</a> to use your Amazon S3, or Flickr, or remote FTP storage</li>  
          </ul>
        </div>
        <div class="span6">
          <h2>Go have fun</h2>
          <p>You are now logged into your new site as <b>admin</b> with full privileges.
Perform these quick tasks to bring your site up to date. This also counts as
a tutorial on how your site works.</p>
          <ul>
            <li>Go to your <a href="../admin/appearance.php" target="_blank">Site Setup</a> page and name your site, you can change your theme while you're there
            <li>Now go to your <a href="../admin/files.php" target="_blank">File Manager</a> page and Rescan Photos, do this whenever you add or change photos in your photo directory
            <li><a href="../search.php&#63;q=unnamed&amp;sort=photos.id" target="_blank">Search</a> for the unnamed pics you just imported and name some of them
            <li><a href="http://fdcl.sourceforge.net/wiki/index.php/Albums" target="_blank">Learn about Albums</a>, and create a few
          </ul>
        </div>
      </div>

      <div class="well">
        <h2>
          You're done 
          <a class="btn btn-primary btn-large" href="../index.php"><i class="icon-arrow-right icon-white"></i> Check out your site</a>
          <a class="btn btn-large" href="https://github.com/fulldecent/cameralife"><i class="icon-star"></i> Star on Github to get updates</a>
          <a class="btn btn-large" href="mailto:cameralifeinstalled@phor.net"><i class="icon-envelope"></i> Send feedback</a>
        </h2>
      </div>
      
    </div>
  </body>
</html>
