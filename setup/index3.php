<?php
/**Displays post installation messages
*@link http://fdcl.sourceforge.net
*@version 2.6.2
*@author Will Entriken <cameralife@phor.net>
*@copyright Copyright (c) 2001-2009 Will Entriken
*@access public
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="common.css">
<title>Camera Life Installation</title>
</head>
<body>

<h1>Post-install configuration</h1>

<?php
    if (!file_exists('../modules/config.inc'))
    {
      echo "<p class='important'>I can't find the file modules/config.inc, things are really gonna be messed up.</p>";
    }
?>
<h2>Get your photos ready</h2>

Collect some photos that you would like to use with the site. Now, either:
<ul>
  <li>Place them in the folder images/photos, or
  <li>Make your existing folder writable and change your photo directory in the
      <a href="../admin/photostore.php" target="_blank">Photo Storage setup</a>
  <li>Use the <a href="../upload.php" target="_blank">Upload Page</a></li>
  <li>Use <a href="http://zwily.com/iphoto/" target="_blank">iPhotoToGallery</a>, <a href="http://www.digikam.org/" target="_blank">digKkam</a>, <a href="http://gallery.menalto.com/wiki/Gallery_Remote" target="_blank">Gallery Remote</a> or any <a href="http://codex.gallery2.org/Other_Clients#Photonator_.28Mac_OS_X.29" target="_blank">other software</a> compatible with the Gallery Remote API</li>
  <li>Advanced setup: change your <a href="../admin/photostore.php" target="_blank">Photo Storage setup</a> to use your Amazon S3, or Flickr, or remote FTP storage</li>

</ul>

<h2>Cover me, I'm going in</h2>

You are now logged into your new site as <i>admin</i> with full privileges.
Perform these quick tasks to bring your site up to date. This also counts as
a tutorial on how your site works.

<ul>
  <li>Go to your <a href="../admin/appearance.php" target="_blank">Site Setup</a> page and name your site, you can change your theme while you're there
  <li>Now go to your <a href="../admin/files.php" target="_blank">File Manager</a> page and Rescan Photos, do this whenever you add or change photos in your photo directory
  <li><a href="../search.php&#63;q=unnamed&amp;sort=photos.id" target="_blank">Search</a> for the unnamed pics you just imported and name some of them
  <li><a href="albums.html" target="_blank">Learn about Albums</a>, and create a few
</ul>

<h2>You're done</h2>

Your site is now set up. Please let me know if this installer was helpful,
email me at: <a href="mailto:cameralife AT phor.net">cameralife-bugs AT phor.net</a> (start subject with "CAMERALIFE-BUGS:").
Now run along and check out <a href="../index.php">your site</a>.

</body>
</html>
