<?php
  # Provides a menu to choose administrative options

  $features=array('database','theme','security');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);

  $numdel = $cameralife->Database->SelectOne('photos','COUNT(*)','status=1');
  $numpri = $cameralife->Database->SelectOne('photos','COUNT(*)','status=2');
  $numupl = $cameralife->Database->SelectOne('photos','COUNT(*)','status=3');
  $numreg = $cameralife->Database->SelectOne('users','COUNT(*)','auth=1');
  $numlog = $cameralife->Database->SelectOne('logs','COUNT(*)','id>'.($cameralife->preferences['core']['checkpoint']+0));
  $numcomments = $cameralife->Database->SelectOne('comments','COUNT(*)','id>'.($cameralife->preferences['core']['checkpointcomments']+0));
?>

<html>
<head>
  <title><?= $cameralife->preferences['core']['sitename'] ?></title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
</head>
<body>

<?php
  $menu = array();
  $menu[] = $cameralife->GetSmallIcon();
  $menu[] = array("name"=>"Stats",
                  "href"=>"../stats.php",
                  'image'=>'small-main');
  $menu[] = array("name"=>"Check for updates",
                  "href"=>"http://fdcl.sourceforge.net/",
                  'image'=>'small-admin');

  $cameralife->Theme->TitleBar("Administration",
                               'admin',
                               FALSE,
                               $menu);

  if ($cameralife->Security->authorize('admin_customize'))
  {
    echo "<tr><td>&nbsp;\n<tr>\n<td colspan=2>";
    $cameralife->Theme->Section('Customize','customize.php','admin-item');
    echo "Customize the theme and choose paths\n";
  }

  if ($cameralife->Security->authorize('admin_customize') && $cameralife->Security->AdministerURL())
  {
    echo "<tr><td>&nbsp;\n<tr>\n<td colspan=2>";
    $cameralife->Theme->Section('User Manager',$cameralife->Security->AdministerURL(),'admin-item');
    echo "User authentication and security management\n";

    if ($numreg)
      echo "<br><font class=\"alert\">$numreg users have registered but not been confirmed</font>";
  }

  if ($cameralife->Security->authorize('admin_customize'))
  {
    echo "<tr><td>&nbsp;\n<tr>\n<td colspan=2>";
    $cameralife->Theme->Section('Log Viewer','logs.php','admin-item');
    echo "View and rollback changes to the site\n";
    if ($numlog)
      echo "<br><font class=\"alert\">There are $numlog logged actions since your last checkpoint</font>";
    else
      echo "<br>No changes have been made since your last checkpoint";
  }

  if ($cameralife->Security->authorize('admin_customize'))
  {
    echo "<tr><td>&nbsp;\n<tr>\n<td colspan=2>";
    $cameralife->Theme->Section('Comment Viewer','comments.php','admin-item');
    echo "View and censor comments on the site\n";
    if ($numlog)
      echo "<br><font class=\"alert\">There are $numcomments comments since your last checkpoint</font>";
    else
      echo "<br>No changes have been made since your last checkpoint";
  }

  if ($cameralife->Security->authorize('admin_file'))
  {
    echo "<tr><td>&nbsp;\n<tr>\n<td colspan=2>";
    $cameralife->Theme->Section('File Manager','files.php','admin-item');
    echo "Maintain photo collection and view private photos\n";

    if ($numdel)
      echo "<br><font class=\"alert\">$numdel photos have been flagged</font>";
    if ($numupl)
      echo "<br><font class=\"alert\">$numupl photos have been uploaded but not reviewed</font>";
    echo "<br><font class=\"alert\">There may be new or deleted files</font>";
  }

  if ($cameralife->Security->authorize('admin_customize'))
  {
    echo "<tr><td>&nbsp;\n<tr>\n<td colspan=2>";
    $cameralife->Theme->Section('Provide Feedback','register.php','admin-item');
    echo "Provide feedback of your experiences with Camera Life";
  }
?>
  </table>
</body>
</html>


