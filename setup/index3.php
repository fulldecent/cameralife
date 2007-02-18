<html>
<head>
<link rel="stylesheet" type="text/css" href="common.css">
<title>Camera Life Installation</title>
</head>
<body>

<h1>Post-install configuration</h1>

<?php
    if (file_exists('../notinstalled.txt'))
    {
      echo "<p class='important'>Delete the file notinstalled.txt, so your site will go live.</p>";
    }
?>
<h2>Get your photos ready</h2>

Collect some photos that you would like to use with the site. Now, either:
<ul>
  <li>Place them in the images/photos, or
  <li>Make your existing folder writable and change your photo directory in the 
      <a href="../admin/customize.php" target="_blank">Site Setup</a> 
</ul>

<h2>Cover me, I'm going in</h2>

You are now logged into your new site as <i>admin</i> with full privileges.
Perform these quick tasks to bring your site up to date. This also counts as
a tutorial on how your site works.

<ul>
  <li>Go to your <a href="../admin/customize.php" target="_blank">Site Setup</a> page and name your site, you can change your theme while you're there
  <li>Now go to your <a href="../admin/files.php" target="_blank">File Manager</a> page and update your
database, do this whenever you add or change photos into your photo directory
  <li><a href="../search.php&#63;q=unnamed&amp;sort=photos.id" target="_blank">Search</a> for the unnamed pics you just imported and name some of them
  <li><a href="albums.html" target="_blank">Learn about Albums</a>, and create a few
</ul>

<h2>You're done</h2>

Your site is now set up. Please let me know if this installer was helpful,
email me at: <a href="mailto:cameralife@phor.net">cameralife-bugs@phor.net</a> (start subject with "CAMERALIFE-BUGS:").
Now run along and check out <a href="../index.php">your site</a>.

</body>
</html>
