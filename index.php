<?php
  $features=array('database','theme','security', 'photostore');
  require "main.inc";

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
  <link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico"/>
  <link rel="alternate" type="application/rss+xml" title="RSS feed of <?= $cameralife->GetPref('sitename') ?>" href="rss.php&#63;q="/>
  <link rel="search" type="application/opensearchdescription+xml" href="opensearch.xml" title="<?= $cameralife->GetPref('sitename') ?> search" />
</head>
<body>
<?php
  $menu = array();

  $menu[] = array('name'=>'Stats',
                  'href'=>'stats.php',
                  'image'=>'small-photo');
  if ($cameralife->Security->GetName())
  {
    $menu[] = array('name'=>'Log out',
                    'href'=>'logout.php',
                    'section'=>'Welcome '.$cameralife->Security->GetName(),
                    'image'=>'small-login');
    $menu[] = array('name'=>"My uploads",
                    'href'=>'folder.php?path=upload/'.$cameralife->Security->GetName().'/',
                    'section'=>'Welcome '.$cameralife->Security->GetName(),
                    'image'=>'small-folder');
    if ($cameralife->Security->AdministerURL())
      $menu[] = array('name'=>'Administer',
                      'href'=>'admin/index.php',
                      'section'=>'Welcome '.$cameralife->Security->GetName(),
                      'image'=>'small-admin');
    if ($cameralife->Security->authorize('admin_customize'))
      $menu[] = array('name'=>'Customize this page',
                      'href'=>'admin/customize.php',
                      'section'=>'Welcome '.$cameralife->Security->GetName(),
                      'image'=>'small-admin');
  }
  else
  {
    $menu[] = array('name'=>'Log in',
                    'href'=>'login.php',
                    'image'=>'small-login');
    $menu[] = array('name'=>'Powered by Camera Life',
                    'href'=>'http://fdcl.sourceforge.net/',
                    'image'=>'small-admin');
  }
  $cameralife->Theme->TitleBar($cameralife->GetPref('sitename'),
                               'main',
                               FALSE,
                               $menu);

  $search = new Search('');
  $counts = $search->GetCounts();
  if ($counts['photos'] == 0) 
    echo '<div class="administrative">Camera Life has been successfully installed on this site. There are currently no photos on this site. For more information on setting up this site and adding photos, see <a href="setup/index3.php"><strong>the Setup page</strong></a>.</div>';

  if ($cameralife->Theme->GetPref('main_thumbnails')) 
  {
    $sections[] = array('name'=>'Random Photos',
                        'page_name'=>'rand',
                        'image'=>'small-photo');
    $sections[] = array('name'=>'Newest Photos',
                        'page_name'=>'newest',
                        'image'=>'small-photo');
    $sections[] = array('name'=>'Newest Folders',
                        'page_name'=>'newest-folders',
                        'image'=>'small-photo');
    $sections[] = array('name'=>'Least Popular Photos',
                        'page_name'=>'unpopular',
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
    
    if ($_GET['page'] == 'popular')
      $cameralife->Theme->Section('All unpopular photos &#187;','search.php?sort=unpopular&page=p&amp;q=');
    else if ($_GET['page'] == 'newest')
      $cameralife->Theme->Section('All new photos &#187;','search.php?sort=newest&amp;page=p&amp;q=');
    else if ($_GET['page'] == 'newfolder')
      $cameralife->Theme->Section('All folders &#187;','folder.php?page=folder&amp;path=');
    else // Random
      $cameralife->Theme->Section('More random photos &#187;','search.php?page=p&amp;sort=rand&amp;q=');
  } // End main thumbnails 
?>
  <table width="100%" cellpadding=0 cellspacing=0>
    <tr valign=top>
      <td width="49%">
<?php if ($cameralife->Theme->GetPref('main_topics')) 
      {
        $cameralife->Theme->Section('Topics');
        if ($cameralife->Security->authorize('admin_albums'))
        {
          echo "<div class='context2'><a href=\"search.php&#63;albumhelp=1\">Create a new album</a></div>\n";
        }
        $topic_query = $cameralife->Database->Select('albums','distinct topic');
  
        while ($topic = $topic_query->FetchAssoc())
        {
          echo "<div class='context'><a href=\"topic.php&#63;name=".urlencode($topic["topic"])."\">";
          $cameralife->Theme->Image('small-topic', array('align'=>'left'));
          echo $topic["topic"]."</a><br>\n";
  
          if ($cameralife->Theme->GetPref('main_topics')==2) // Link a couple albums
          {
            $where = "topic='".$topic['topic']."'";
            $extra = 'ORDER BY RAND() LIMIT '.$cameralife->Theme->GetPref('main_topics_n');
            $album_query = $cameralife->Database->Select('albums','id,name',$where,$extra);
            echo "<div class='context2'>(";
            $first = true;
  
            while ($album = $album_query->FetchAssoc())
            {
              if (!$first) echo ", ";
              $first = FALSE;
              echo "<a href=\"album.php&#63;id=".$album["id"]."\">".
                    $album['name']."</a>";
            }
            echo ")</div>\n";
          }
          echo "</div>\n";
        }
      } /* end main_topics */ 
?>
      <td width="2%">
      <td width="49%">
        <form action="search.php" method="get">
          <?php $cameralife->Theme->Section('Search'); ?>
          <input type="text" name="q" value="" size="20">
          <input type="image" src="<?= $cameralife->Theme->ImageURL('search') ?>" value="search">
        </form>
<?php if ($cameralife->Theme->GetPref('main_folders') == 1) 
      {

        $cameralife->Theme->Section('Browse');
  
        $root = new Folder();
        $folders = $root->GetDescendants($cameralife->Theme->GetPref('main_folders_n'));

        foreach ($folders as $folder)
        {
          echo "<div class='context'><a href=\"folder.php&#63;path=".urlencode($folder->Path())."\"> ";
          $cameralife->Theme->Image('small-folder', array('align'=>'middle'));
          echo $folder->Basename()."</a></div>\n";
        }
        echo "<div class='context'><a href=\"folder.php&#63;page=folder&amp;path=\">";
        $cameralife->Theme->Image('small-folder', array('align'=>'middle'));
        echo "... all folders</a></div>";
      } /* end folders */
  
      if ($cameralife->Security->authorize('photo_upload'))
      {
        $cameralife->Theme->Section('Upload', 'upload.php');
        echo "<div class='context'><a href=\"upload.php\">";
        $cameralife->Theme->Image('small-photo', array('align'=>'middle'));
        echo "  Upload photos to ".$cameralife->GetPref('sitename');
        echo "</a></div>";
      }
      else
      {
        $cameralife->Theme->Section('Upload');
        echo "In order to upload, you must register or login.";
      }
?>
    </table>
  </body>
</html>


