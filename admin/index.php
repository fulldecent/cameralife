<?php
  # Provides a menu to choose administrative options
/**Displays the main admin page
*Provides a menu to choose administrative options
*@link http://fdcl.sourceforge.net
 *@version 2.6.3b4
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
*/
/**
*/
  $features=array('database','security', 'photostore');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);

  $numdel = $cameralife->Database->SelectOne('photos','COUNT(*)','status=1');
  $numpri = $cameralife->Database->SelectOne('photos','COUNT(*)','status=2');
  $numupl = $cameralife->Database->SelectOne('photos','COUNT(*)','status=3');
  $numreg = $cameralife->Database->SelectOne('users','COUNT(*)','auth=1');
  $numlog = $cameralife->Database->SelectOne('logs','COUNT(*)','id>'.($cameralife->GetPref('checkpointlogs')+0));
  $numcomments = $cameralife->Database->SelectOne('comments','COUNT(*)','id>'.($cameralife->GetPref('checkpointcomments')+0));
?>
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <link rel="stylesheet" href="admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body>

<div id="header">
<h1>Site Administration</h1>
<?php
  $home = $cameralife->GetIcon('small');
  echo '<a href="'.$home['href']."\"><img src=\"".$cameralife->IconURL('small-main')."\">".$home['name']."</a>\n";
?> |
<a href="../stats.php"><img src="<?= $cameralife->IconURL('small-photo')?>">Stats</a> |

<?php
  if (file_exists('../.svn'))
    $svn = exec('svnversion '.$cameralife->base_dir);
  else
    $svn = 'not versioned';
  
  if ($svn != 'not versioned')
  {
    echo "Camera Life version <strong>svn:$svn</strong> | ";

    if (!$_GET['svn'])
    {
      echo " <a href=\"?svn=yes\">check for svn update</a>";
    } 
    else
    {
      $newest = file_get_contents('http://fdcl.svn.sourceforge.net/svnroot/fdcl/');
      ereg('Revision ([0-9]+):', $newest, $regs);
      echo "Latest is <strong>".$regs[1]."</strong> ";
      echo "<a href=\"http://fdcl.svn.sourceforge.net/viewvc/fdcl/trunk/?view=log#rev80\">view diffs</a>";

      echo "<pre>";
       passthru('svn log '.$cameralife->base_dir.' -r base:head');
      echo "</pre>";
    }
  }
  else
  {
    echo "Camera Life version <strong>".$cameralife->version."</strong> | ";

    if (!$_GET['svn'])
    {
      echo " <a href=\"?svn=yes\">check latest version</a>";
    } 
    else
    {
      # We collect your ip and version
      $newest = file_get_contents('http://fdcl.sourceforge.net/check.php?a='.$cameralife->version);
      echo "Latest is <strong>".$newest."</strong> ";
      echo "<a href=\"http://fdcl.sourceforge.net\">get it</a>";
    }
  }
?>
</div>

<?php

  if ($cameralife->Security->authorize('admin_customize'))
  {
    echo "<h2><a href=\"appearance.php\">Appearance</a></h2>\n";
    echo "<p>Choose a theme and iconset</p>\n";

    # Upgrade hack
    if (is_dir($cameralife->base_dir."/images/scaled/"))
    {
      echo "<p class=\"alert\">Note: images/scaled is no longer used, scaled photos now go in your \"Automatically cached photos\" folder.</p>\n";
    }
    if (is_dir($cameralife->base_dir."/images/thumbnail/"))
    {
      echo "<p class=\"alert\">Note: images/thumbnail is no longer used, thumbnails now go in your \"Automatically cached photos\" folder.</p>\n";
    }
    if (is_dir($cameralife->base_dir."/images/modified/"))
    {
      echo "<p class=\"alert\">Note: images/modified is no longer used, modified photos now go in your \"Automatically cached photos\" folder. Make sure you Update Your Database first (in File Manager), so your modified files are copied over.</p>\n";
    }
  }

  if ($cameralife->Security->authorize('admin_customize') && $cameralife->Security->AdministerURL())
  {
    echo '<h2><a href="'.$cameralife->Security->AdministerURL()."\">User Manager</a></h2>\n";
    echo "<p>User authentication and security management</p>\n";

    if ($numreg)
      echo "<p class=\"alert\">$numreg users have registered but not been confirmed</p>\n";
  }

  if ($cameralife->Security->authorize('admin_customize'))
  {
    echo "<h2><a href=\"logs.php\">Log Viewer</a></h2>\n";
    echo "<p>View and rollback changes to the site</p>\n";
    if ($numlog)
      echo "<p class=\"alert\">There are $numlog logged actions since your last checkpoint</p>";
    else
      echo "<p>No changes have been made since your last checkpoint</p>";
  }

  if ($cameralife->Security->authorize('admin_customize'))
  {
    echo "<h2><a href=\"comments.php\">Comment Viewer</a></h2>\n";
    echo "<p>View and censor comments on the site</p>\n";
    if ($numlog)
      echo "<p class=\"alert\">There are $numcomments comments since your last checkpoint</p>";
    else
      echo "<p>No changes have been made since your last checkpoint</p>";
  }

  if ($cameralife->Security->authorize('admin_file'))
  {
    echo "<h2><a href=\"files.php\">File Manager</a></h2>\n";
    echo "<p>Maintain photo collection and view private photos</p>\n";

    if ($numdel)
      echo "<p class=\"alert\">$numdel photos have been flagged</p>";
    if ($numupl)
      echo "<p class=\"alert\">$numupl photos have been uploaded but not reviewed</p>";
  }

  if ($cameralife->Security->authorize('admin_file'))
  {
    echo "<h2><a href=\"photostore.php\">Photo Storage</a></h2>\n";
    echo "<p>Setup where your photos are stored: in a local folder, a remote server, Amazon S3, ...</p>\n";
  }

  if ($cameralife->Security->authorize('admin_customize'))
  {
    echo "<h2><a href=\"register.php\">Provide Feedback</a></h2>\n";
    echo "<p>Provide feedback of your experiences with Camera Life</p>";
  }
?>
</body>
</html>


