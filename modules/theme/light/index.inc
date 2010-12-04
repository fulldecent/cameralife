<?php
/** Indexing of photos
 *
 *Page appears after successful installation and prompts the user to add photos
 *
 *<b>Note</b> $cameralife set by ShowPage()
 *@link http://fdcl.sourceforge.net
 *@version 2.6.3
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 */

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');
  $cameralife->Theme->Header($cameralife->GetPref('sitename'), 'main');

  $search = new Search('');
  $counts = $search->GetCounts();

  if ($counts['photos'] == 0)
    echo '<div class="administrative">Camera Life has been successfully installed on this site. There are currently no photos on t
his site. For more information on setting up this site and adding photos, see <a href="setup/index3.php"><strong>the Setup page</s
trong></a>.</div>';

  if (isset($_GET['page']))
    list($sort,$type) = explode('-', $_GET['page']);

  $search->SetPage(0, $cameralife->Theme->GetPref('main_thumbnails_n'));
  $search->SetSort('rand'); # rand, newest, newest-folders, unpopular

  if (isset($type) && $type == 'folders')
    $results = $search->GetFolders();
  else
    $results = $search->GetPhotos();


?>

<div id="sidebar">
  <h1>Random Photos</h1>
<?php
  foreach ($results as $result)
  {
    $icon = $result->GetIcon();
    echo '<div class="item"><a href="'.$icon['href'].'"><img src="'.$icon['image'].'" alt="'.$icon['name'].'" /></a>';
    echo '<a style="clear: left" href="'.$icon['href'].'">'.$icon['name'].'</a>';
    echo '<span class="description">'.$result->Get('created').'</span></div>';
  }
?>
</div>


  <div id="text" >

    <h1>Random Folders</h1>
    <ul>
<?php
        $root = new Folder();
        $root->SetSort('newest');
        $folders = $root->GetDescendants($cameralife->Theme->GetPref('main_folders_n'));

        foreach ($folders as $folder)
        {
          $icon = $folder->GetIcon('small');
          echo "<li><h3><a href=\"".$icon['href']."\"> ";
          echo $icon['name']."</a></h3>\n";

          $folder->SetSort('rand');
          $folder->SetPage(0, $cameralife->Theme->GetPref('main_folders_m'));
          $photos = $folder->GetPhotos();
          echo "<div>";
          foreach($photos as $result)
          {
            $icon = $result->GetIcon();
            echo '<div class="framemini" style="-moz-transform:rotate('.rand(-10,10).'deg); -webkit-transform:rotate('.rand(-10,10).'deg);"><a href="'.$icon['href'].'"><img width="'.($icon['width']/2).'" src="'.$icon['image'].'" alt="'.$icon['name'].'" /></a></div>';
          }
          echo "</div></li>";
        }
        $root = new Folder('');
        $icon = $root->GetIcon();
        echo "<li><h3><a href=\"".$icon['href']."\">... show all folders</a></h3></li>";
?>
    </ul>

    <h1>Photo Albums</h1>
    <p>Find interesting photo quickly by looking under these topics. 
    <?php if ($cameralife->Security->authorize('admin_albums')) echo "<a href=\"search.php&#63;albumhelp=1\">Create a new album</a>."; ?> 
    </p>

    <ul>
<?php

        foreach (Topic::GetTopics() as $thetopic)
        {
          $topic = new Topic($thetopic);
          $icon = $topic->GetIcon('small');

          echo "<li><strong><a href=\"".$icon['href']."\">";
          echo $icon['name']."</a></strong>: \n";

          if ($cameralife->Theme->GetPref('main_topics')==2) // Link a couple albums
          {
            $topic->SetSort('rand');
            $topic->SetPage(0, $cameralife->Theme->GetPref('main_topics_n'));

            echo "<div class='context2'>(";
            $count = 0;
            foreach($topic->GetAlbums() as $album)
            {
              if ($count++) echo ", ";
              $icon = $album->GetIcon();
              echo "<a href=\"".$icon['href']."\">".
                    $icon['name']."</a>";
            }
          }
          echo ", ...)</div></li>\n";
        }

?>
    </ul>

    <h1>Upload</h1>
<?php
      if ($cameralife->Security->authorize('photo_upload'))
      {
        echo "<p><a href=\"upload.php\">";
        echo "  Upload photos to ".$cameralife->GetPref('sitename');
        echo "</a></p>";
      }
      else
      {
        echo "<p>In order to upload, you must register or login.</p>";
      }

  $cameralife->Theme->Footer();
?>
