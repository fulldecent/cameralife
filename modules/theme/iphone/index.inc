<?php
  # $cameralife set by ShowPage()

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');

  $_GET['page'] or $_GET['page'] = 'rand';
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
  <link rel="alternate" type="application/rss+xml" title="RSS feed of <?= $cameralife->GetPref('sitename') ?>" href="rss.php&#63;q="/>
  <link rel="search" type="application/opensearchdescription+xml" href="opensearch.xml" title="<?= $cameralife->GetPref('sitename') ?> search" />
</head>
<body>

<h1><?= $cameralife->GetPref('sitename') ?></h1>
        <form action="search.php" method="get">
          <input type="text" name="q" value="" size="20">
          <input type="image" src="<?= $cameralife->Theme->ImageURL('search') ?>" value="search">
        </form>


<?= $cameralife->GetPref('sitename') ?>
iphone
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

    list($sort,$type) = explode('-', $_GET['page']);

    $search->SetPage(0, $cameralife->Theme->GetPref('main_thumbnails_n'));
    $search->SetSort($sort);

    if ($type == 'folders')
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
        $topic_query = $cameralife->Database->Select('albums','DISTINCT topic');
  
        while ($topic = $topic_query->FetchAssoc())
        {
          $topic = new Topic($topic['topic']);
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
//TODO use folder's Icon
          echo "<div class='context'><a href=\"".$icon['href']."\"> ";
          $cameralife->Theme->Image('small-folder', array('align'=>'middle'));
          echo $icon['name']."</a></div>\n";
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


