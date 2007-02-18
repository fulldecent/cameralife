<?php
  # Sets the options for your site...

  $features=array('database','theme','security');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);

  $cameralife->Security->authorize('admin_customize', 1); // Require

  $_GET['page'] or $_GET['page'] = 'setup';

  foreach ($_POST as $key => $val)
  {
    if ($key == 'UseTheme')
    {
      $cameralife->preferences['core']['theme'] = $val;
      header('Location: customize.php?page=themes');
    }
    else
      $cameralife->preferences['core'][$key] = rtrim($val,'/');
  }
  $cameralife->SavePreferences();

  function check_dir($dir)
  {
    global $cameralife;

    if ($dir[0] != '/')
      $dir = $cameralife->base_dir."/$dir/";
    if (!is_dir($dir) )
      echo "<p class=\"alert\">WARNING: $dir is not a directory!</p>";
	elseif (!is_writable($dir))
      echo "<p class=\"alert\">WARNING: $dir is not writable!</p>";
  }

  $all_themes = array();
?>

<html>
<head>
  <title><?= $cameralife->preferences['core']['sitename'] ?> - Customize</title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
</head>
<body>
<form method="post" action="http://<?= $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'] ?>&#63;page=<?= $_GET['page']?>">

<?php
  $menu = array();
  $menu[] = array("name"=>$cameralife->preferences['core']['siteabbr'],
                  "href"=>"../index.php",
                  'image'=>'small-main');
  $menu[] = array("name"=>"Administration",
                  "href"=>"index.php",
                  'image'=>'small-admin');
  $menu[] = array("name"=>"Get more themes",
                  "href"=>"http://fdcl.sourceforge.net/index.php&#63;content=themes",
                  'image'=>'small-admin');

  $cameralife->Theme->TitleBar("Site Customization",
                               'admin',
                               "Set permissions and setup site",
                               $menu);

  $sections[] = array('name'=>'Site setup',
                      'page_name'=>'setup',
                      'image'=>'small-admin');
  $sections[] = array('name'=>'Main page layout',
                      'page_name'=>'layout',
                      'image'=>'small-admin');
  $sections[] = array('name'=>'Themes',
                      'page_name'=>'theme',
                      'image'=>'small-admin');

  $cameralife->Theme->MultiSection($sections);

  if ($_GET['page'] == 'setup') { 
    check_dir($cameralife->preferences['core']['photo_dir']);
    check_dir($cameralife->preferences['core']['cache_dir']);
    check_dir($cameralife->preferences['core']['deleted_dir']);
?>
  <table align="center" cellspacing="2" border=1 width="100%">
    <tr>
      <th colspan=2>
        Site parameters - <i>contact info, preferences, etc...</i>
    <tr>
      <td>
        Site name
      <td width=100>
        <input type=text name="sitename" size=30 
                value="<?= $cameralife->preferences['core']['sitename'] ?>">
    <tr>
      <td>
        Site abbreviation (used to refer to the main page)
      <td>
        <input type=text name="siteabbr" size=30 
                value="<?= $cameralife->preferences['core']['siteabbr'] ?>">
    <tr>
      <td>
        Owner E-mail address (shown if something goes wrong)
      <td>
        <input type=text name="owner_email" size=30 
                value="<?= $cameralife->preferences['core']['owner_email'] ?>">
  </table>

  <table align="center" cellspacing="2" border=1 width="100%">
    <tr>
      <th colspan=2>
        Site directories - <i>relative to the main page</i>
    <tr>
      <td>
        Main photo directory
      <td width=100>
        <input type=text name="photo_dir" size=30
                value="<?= $cameralife->preferences['core']['photo_dir'] ?>">
    <tr>
      <td>
        Automatically cached photos
      <td>
        <input type=text name="cache_dir" size=30
                value="<?= $cameralife->preferences['core']['cache_dir'] ?>">
    <tr>
      <td>
        Deleted photos (...where they go when you "erase" them)
      <td>
        <input type=text name="deleted_dir" size=30
                value="<?= $cameralife->preferences['core']['deleted_dir'] ?>">
  </table>

  <p>
    <input type=submit value="Save and Validate changes">
    <a href="customize.php">(Revert to last saved)</a>
  </p>
<?php } else if ($_GET['page'] == 'layout') { ?>
  <table align="center" cellspacing="2" border=1 width="100%">
    <tr>
      <th colspan=3>
        Main page setup - <i>feng shui</i>
    <tr>
      <td>
        Random, most popular, or latest photos... on main page
      <td width=100>
        <select name="main_thumbnails">
          <option <?php if($cameralife->preferences['core']['main_thumbnails'] == "0") echo "selected" ?> value="0">Don't show</option>
          <option <?php if($cameralife->preferences['core']['main_thumbnails'] == "1") echo "selected" ?> value="1">Show N thumbnails</option>
        </select>
      <td width=100>
        N=<input type="text"  name="main_thumbnails_n" size=10
                value="<?= $cameralife->preferences['core']['main_thumbnails_n'] ?>">
    <tr>
      <td>
        Photo album topics
      <td width=100>
        <select name="main_topics">
          <option <?php if($cameralife->preferences['core']['main_topics'] == "0") echo "selected" ?> value="0">Don't show</option>
          <option <?php if($cameralife->preferences['core']['main_topics'] == "1") echo "selected" ?> value="1">Show all topics</option>
          <option <?php if($cameralife->preferences['core']['main_topics'] == "2") echo "selected" ?> value="2">Show all topics, and N albums each</option>
        </select>
      <td>
        N=<input type="text"  name="main_topics_n" size=10
                      value="<?= $cameralife->preferences['core']['main_topics_n'] ?>">
    <tr>
      <td>
        Folders on main page
      <td width=100>
        <select name="main_folders">
          <option <?php if($cameralife->preferences['core']['main_folders'] == "0") echo "selected" ?> value="0">Don't show</option>
          <option <?php if($cameralife->preferences['core']['main_folders'] == "1") echo "selected" ?> value="1">Show N random folders</option>
        </select>
      <td>
        N=<input type="text"  name="main_folders_n" size=10
                      value="<?= $cameralife->preferences['core']['main_folders_n'] ?>">
  </table>

  <p>
    <input type=submit value="Save and Validate changes">
    <a href="customize.php&#63;page=layout">(Revert to last saved)</a>
  </p>

<?php 
  } else { /* page == 'themes' */ 
echo '</form>';

    echo '<form method="post" action="controller_prefs.php">';
    echo '<input type="hidden" name="target" value="'.$_SERVER['PHP_SELF'].'&#63;page='.$_GET['page'].'" />';
    echo '<input type="hidden" name="module1" value="core" />';
    echo '<input type="hidden" name="param1" value="theme" />';

    echo "<fieldset>";
    echo "<legend>Choose a theme</legend>";

    $fd = opendir($cameralife->base_dir."/modules/theme/");
    while (($file = readdir($fd)) !== FALSE)
    {
      if ($file[0] == '.')
        continue;
      if (!is_dir($cameralife->base_dir."/modules/theme/$file"))
        continue;
      if (!is_file($cameralife->base_dir."/modules/theme/$file/theme-info.php"))
        continue;
  
      include($cameralife->base_dir."/modules/theme/$file/theme-info.php");
  
      echo "<tr><td>\n";
  
      if ($cameralife->preferences['core']['theme'] == $file)
        echo "<div style=\"border:2px solid blue; padding:0.5em\" width=\"100%\">\n";
      else
        echo "<div style=\"border:2px solid gray; padding:0.5em\" width=\"100%\">\n";
  
      echo " <b>$theme_name</b> - <i>version $theme_version by $theme_author</i>";
      echo " <input type=submit name=\"value1\" value=\"".basename($file)."\" />\n";
      echo "</div>\n";
  
      echo "<tr><td>&nbsp;\n";
      flush();
    }
    echo "</fieldset></form>";

    echo '<form method="post" action="controller_prefs.php">';
    echo '<input type="hidden" name="target" value="'.$_SERVER['PHP_SELF'].'&#63;page='.$_GET['page'].'" />';
    echo '<input type="hidden" name="module1" value="core" />';
    echo '<input type="hidden" name="param1" value="iconset" />';
    echo "<fieldset>";
    echo "<legend>Choose an iconset</legend>";

    $fd = opendir($cameralife->base_dir.'/modules/iconset/');
    while (($file = readdir($fd)) !== FALSE)
    {
      if ($file[0] == '.')
        continue;
      if (!is_dir($cameralife->base_dir."/modules/iconset/$file"))
        continue;
      if (!is_file($cameralife->base_dir."/modules/iconset/$file/iconset-info.php"))
        continue;
  
      include($cameralife->base_dir."/modules/iconset/$file/iconset-info.php");
  
      echo "<tr><td>\n";
  
      if ($cameralife->preferences['core']['iconset'] == $file)
        echo "<div style=\"border:2px solid blue; padding:0.5em\" width=\"100%\">\n";
      else
        echo "<div style=\"border:2px solid gray; padding:0.5em\" width=\"100%\">\n";
  
      echo " <b>$iconset_name</b> - <i>version $iconset_version by $iconset_author</i>";
      echo " <input type=submit name=\"value1\" value=\"$file\" />\n";
      echo "</div>\n";
  
      echo "<tr><td>&nbsp;\n";
      flush();
    }
    echo "</fieldset>";
  }
?>

</form>
</body>
</html>

