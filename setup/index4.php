<?php
/**
 * Displays post installation notifcation messages
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
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
            <li><a>1. Login</a></li>
            <li><a>2. Setup</a></li>
            <li class="active"><a>3. Use Camera Life</a></li>
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
        <h2 class="text-success"><i class="glyphicon glyphicon-ok"></i> You are running Camera Life <?php readfile(
                '../VERSION'
            ) ?></h2>

        <p><a class="btn btn-default btn-large" target="_blank" href="https://github.com/fulldecent/cameralife"><i
                    class="glyphicon glyphicon-star"></i> Star us on GitHub</a> to get important security updates</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h2>Get your photos ready</h2>

            <p>Collect some photos for your new site. Now, either:</p>
            <ul>
                <li>Place them in the folder <code>images/photos</code>
                <li>Make your existing folder writable and set your photo directory in the
                    <a href="../admin/filestore.php" target="_blank">File Storage setup</a>
                <li>Use the <a href="../upload.php" target="_blank">Upload Page</a></li>
                <li>Use <a href="https://itunes.apple.com/us/app/ftpmanager-free/id525959186?mt=8" target="_blank">your
                        iPhone</a>, or other device to upload directly to your site
                </li>
                <!--            <li>Use rsync to photos from your desktop to your server</li> -->
                <li>Change your <a href="../admin/filestore.php" target="_blank">File Storage setup</a> to use Amazon
                    S3, or remote FTP storage (advanced)
                </li>
            </ul>
        </div>
        <div class="col-md-6">
            <h2>Go have fun</h2>

            <p>You are now logged into your new site as <b>admin</b> with full privileges.
                Perform these quick tasks to bring your site up to date. This also counts as
                a tutorial on how your site works.</p>
            <ul>
                <li>Go to your <a href="../admin/appearance.php" target="_blank">Site Setup</a> page and name your site,
                    you can change your theme while you're there
                <li>Now go to your <a href="../admin/files.php" target="_blank">File Manager</a> page and Rescan Photos,
                    do this whenever you add or change photos in your photo directory if it doesn't happen automatically
                <li><a href="../search.php&#63;q=unnamed&amp;sort=photos.id" target="_blank">Search</a> for the unnamed
                    pics you just imported and name some of them
                <li><a href="https://github.com/fulldecent/cameralife/wiki/Albums" target="_blank">Learn about
                        Albums</a>, and create a few
            </ul>
        </div>
    </div>

    <div class="well">
        <h2>
            You're done
            <a class="btn btn-primary btn-lg" href="../index.php" target="_blank"><i
                    class="glyphicon glyphicon-arrow-right"></i> Check out your site</a>
            <em>and login with the same OpenID</em>
        </h2>
    </div>

</div>
</body>
</html>
