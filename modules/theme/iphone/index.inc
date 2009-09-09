<?php
/** Indexing of photos
*
*Page appears after successful installation and prompts the user to add photos
*
*<b>Note</b> $cameralife set by ShowPage()
*@link http://fdcl.sourceforge.net
  *@version 2.6.3b3
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
*/
/**
*/
  # $cameralife set by ShowPage()

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');

  if ($_GET['page'] == 'newest')
    $page = 'newest';
  elseif ($_GET['page'] == 'newest-folders')
    $page = 'newest-folders';
  else
    $page = 'rand';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--
  Welcome to <?= $cameralife->GetPref('sitename') ?>!
  This site is maintained by: <?= $cameralife->GetPref('owner_email') ?>

  This site is powered by Camera Life version <?= $cameralife->version ?> by Will Entriken "Full Decent",
  available at: http://fdcl.sourceforge.net
-->
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" type="text/css" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
  <meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=yes;" />
  <link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico"/>
</head>
<body>

<h1><?= $cameralife->GetPref('sitename') ?></h1>
        <form action="search.php" method="get">
          <input type="text" name="q" value="" size="20">
          <input type="image" src="<?= $cameralife->Theme->ImageURL('search') ?>" value="search">
        </form>

<?php

  $search = new Search('');
  $counts = $search->GetCounts();
  if ($counts['photos'] == 0) 
    echo '<div class="administrative">Camera Life has been successfully installed on this site. There are currently no photos on this site. For more information on setting up this site and adding photos, see <a href="setup/index3.php"><strong>the Setup page</strong></a>.</div>';

  if ($cameralife->Theme->GetPref('main_thumbnails')) 
  {
    $sections[] = array('name'=>'Newest Photos',
                        'page_name'=>'newest',
                        'image'=>'small-photo');
    $sections[] = array('name'=>'Newest Folders',
                        'page_name'=>'newest-folders',
                        'image'=>'small-photo');

    $cameralife->Theme->MultiSection($sections);

    $search->SetPage(0, $cameralife->Theme->GetPref('main_thumbnails_n'));
    $search->SetSort($page == 'rand' ? 'rand' : 'newest');

    if ($page == 'newest-folders')
      $results = $search->GetFolders();
    else
      $results = $search->GetPhotos();

    $cameralife->Theme->Grid($results);
    
  } // End main thumbnails 
?>
  <table width="100%" cellpadding=0 cellspacing=0>
    <tr valign=top>
      <td width="49%">
<?php if ($cameralife->Theme->GetPref('main_topics')) 
      {
        $cameralife->Theme->Section('Topics');
  
        foreach (Topic::GetTopics() as $thetopic)
        {
          $topic = new Topic($thetopic);
          $icon = $topic->GetIcon('small');

          echo "<div class='context'><a href=\"".$icon['href']."\">";
          $cameralife->Theme->Image('small-topic', array('align'=>'left'));
          echo $icon['name']."</a><br>\n";
  
          echo "</div>\n";
        }
      } /* end main_topics */ 
?>
      <td width="2%">
      <td width="49%">
<?php if ($cameralife->Theme->GetPref('main_folders') == 1) 
      {

        $cameralife->Theme->Section('Browse');
  
        $root = new Folder();
        $folders = $root->GetDescendants($cameralife->Theme->GetPref('main_folders_n'));

        foreach ($folders as $folder)
        {
          $icon = $folder->GetIcon('small');
          echo "<li><a href=\"".$icon['href']."\"> <img src=\"";
          echo $icon['image']."\" alt=\"folder\"> ".$icon['name']."</a></li>\n";
        }
        $root = new Folder('');
        $icon = $root->GetIcon();
        echo "<div class='context'><a href=\"".$icon['href']."\">";
        $cameralife->Theme->Image('small-folder', array('align'=>'middle'));
        echo "... all folders</a></div>";
      } /* end folders */
  
?>
    </table>
  </body>
</html>


