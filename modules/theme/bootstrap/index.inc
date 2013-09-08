<?php
/** Indexing of photos
 *
 * NOTE: $cameralife set by ShowPage()
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

$cameralife->GetFeature('security');
$cameralife->GetFeature('photostore');
$cameralife->Theme->Header($cameralife->GetPref('sitename'));

$_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 'rand';
list($sort,$type) = explode('-', $_GET['page'].'-');
$search = new Search('');
$search->SetPage(0, 11);
$search->SetSort($sort); # rand, newest, newest-folders, unpopular
$counts = $search->GetCounts();

if ($type == 'folders')
    $results = $search->GetFolders();
else
    $results = $search->GetPhotos();

if ($counts['photos'] == 0)
    echo '<div class="alert alert-success"><strong>Note!</strong> Camera Life has been successfully installed on this site. There are currently no photos on this site. For more information on setting up this site and adding photos, see <a href="setup/index3.php"><strong>the Setup page</strong></a>.</div>';
?>

<div class="well">
    <ul class="nav nav-pills">
      <li <?= $_GET['page']=='rand'?'class="active"':'' ?>><a href="?page=rand">Random photos</a></li>
      <li <?= $_GET['page']=='newest'?'class="active"':'' ?>><a href="?page=newest">Newest photos</a></li>
      <li <?= $_GET['page']=='newest-folders'?'class="active"':'' ?>><a href="?page=newest-folders">Newest folders</a></li>
      <li <?= $_GET['page']=='unpopular'?'class="active"':'' ?>><a href="?page=unpopular">Unpopular</a></li>
    </ul>
    <div style="height: 170px" class="clipbox">
<?php
foreach ($results as $result) {
    $icon = $result->GetIcon();
    echo '<div class="l1" style="-moz-transform:rotate('.rand(-10,10).'deg); -webkit-transform:rotate('.rand(-10,10).'deg)">';
    echo '<a href="'.$icon['href'].'" class="l2">';
    echo '<img alt="'.$icon['name'].'" src="'.$icon['image'].'" class="l3">';
    if (isset($icon['width']) && isset($icon['width']))
        echo '<div class="l4" style="width:'.($icon['width']).'px">'.$icon['name'].'</div>';
    else
        echo '<div class="l4">'.$icon['name'].'</div>';
    echo '</a>';
    echo '</div>';
}
?>    
    </div>
</div>


<div class="row">
  <div class="col-sm-6">
    <h2>Folders</h2>
    <table class="table">
<?php
        $root = new Folder();
        $root->SetSort('newest');
        $folders = $root->GetDescendants($cameralife->Theme->GetPref('main_folders_n'));

        foreach ($folders as $folder)
        {
          $icon = $folder->GetIcon('small');
          echo "<tr><td><h3><a href=\"".$icon['href']."\"> ";
          echo $icon['name']."</a></h3>\n";

          $folder->SetSort('rand');
          $folder->SetPage(0, 11);
          $photos = $folder->GetPhotos();
          echo '<div style="height:80px" class="clipbox">';
          foreach($photos as $result)
          {
            $icon = $result->GetIcon();
            echo '<div class="l1" style="-moz-transform:rotate('.rand(-10,10).'deg); -webkit-transform:rotate('.rand(-10,10).'deg);">';
            echo '<a class="minipolaroid" href="'.$icon['href'].'">';
            echo '<img width="'.intval($icon['width']/2).'" src="'.$icon['image'].'" alt="'.$icon['name'].'" />';
            echo '</a>';
            echo '</div>';
          }
          echo "</div>";
        }
        $root = new Folder('');
        $icon = $root->GetIcon();
        echo "<tr><td><h3><a href=\"".$icon['href']."\">... show all folders</a></h3>";
?>
    </table>
  </div>
  <div class="col-sm-6">
    <h2>Photo Albums</h2>
    <table class="table">
<?php

        foreach (Topic::GetTopics() as $thetopic)
        {
          $topic = new Topic($thetopic);
          $icon = $topic->GetIcon('small');

          echo "<tr><td><h3><a href=\"".$icon['href']."\">";
          echo $cameralife->Theme->Image('small-topic');
          echo $icon['name']."</a></h3>\n";

          if ($cameralife->Theme->GetPref('main_topics')==2) // Link a couple albums
          {
            $topic->SetSort('rand');
            $topic->SetPage(0, 4);

            echo '<div style=" overflow: hidden; white-space: nowrap; text-overflow: ellipsis; ">';
            $count = 0;
            foreach($topic->GetAlbums() as $album)
            {
              if ($count++) echo ", ";
              $icon = $album->GetIcon();
              echo "<a href=\"".$icon['href']."\">".
                    $icon['name']."</a>";
            }
          }
          echo ", ...</div>\n";
        }
          echo "<tr><td><h3><a href=\"search.php&#63;albumhelp=1\">... create an album</a></h3>";

?>
    </table>
  </div>
</div>

<?php
  $cameralife->Theme->Footer();
?>
