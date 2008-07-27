<?php
  # $cameralife set by ShowPage()

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');

  $cameralife->Theme->Header($cameralife->GetPref('sitename'), 'main');

  $search = new Search('');
  $counts = $search->GetCounts();

  if ($counts['photos'] == 0)
    echo '<div class="administrative">Camera Life has been successfully installed on this site. There are currently no photos on t
his site. For more information on setting up this site and adding photos, see <a href="setup/index3.php"><strong>the Setup page</s
trong></a>.</div>';

  list($sort,$type) = explode('-', $_GET['page']);

  $search->SetPage(0, $cameralife->Theme->GetPref('main_thumbnails_n'));
  $search->SetSort('rand'); # rand, newest, newest-folders, unpopular

  if ($type == 'folders')
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
    echo '<a style="clear: left" href="'.$icon['href'].'">'.$result->Get('created').'</a><span class="description">'.$icon['name'].'</span></div>';
#var_dump($icon);
  }
?>
</div>

          <div id="text" >
                <h1>Photo Albums</h1>
                <p>Find interesting photo quickly by looking under these topics. 
<?php if ($cameralife->Security->authorize('admin_albums')) echo "<a href=\"search.php&#63;albumhelp=1\">Create a new album</a>."; ?> 
</p>

                <ul>
<?php
        $topic_query = $cameralife->Database->Select('albums','DISTINCT topic');
        while ($topic = $topic_query->FetchAssoc())
        {
          $topic = new Topic($topic['topic']);
          $icon = $topic->GetIcon('small');

          echo "<li><strong><a href=\"".$icon['href']."\">";
          echo $icon['name']."</a></strong>: \n";

          if ($cameralife->Theme->GetPref('main_topics')==2) // Link a couple albums
          {
            $where = "topic='".$topic->Get('name')."'";
            $extra = 'ORDER BY RAND() LIMIT '.$cameralife->Theme->GetPref('main_topics_n');
            $album_query = $cameralife->Database->Select('albums','id',$where,$extra);
            $count = 0;

            while ($album = $album_query->FetchAssoc())
            {
              if ($count++) echo ", ";
              $album = new Album($album['id']);
              $icon = $album->GetIcon();
              echo "<a href=\"".$icon['href']."\">".
                    $icon['name']."</a>";
            }
          }
          echo ", ...</li>\n";
        }

?>
                </ul>
                <h1>Browse Folders</h1>
                <p>You can browse photos here in heirarichal order.</p>
                <ul>

<?php
        $root = new Folder();
        $folders = $root->GetDescendants($cameralife->Theme->GetPref('main_folders_n'));

        foreach ($folders as $folder)
        {
          $icon = $folder->GetIcon('small');
//TODO use folder's Icon
          echo "<li><a href=\"".$icon['href']."\"> ";
          echo $icon['name']."</a></li>\n";
        }
        $root = new Folder('');
        $icon = $root->GetIcon();
        echo "<li><a href=\"".$icon['href']."\">... show all folders</a></li>";
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
