<?php
/** Indexing of photos
 *
 * NOTE: $cameralife set by showPage()
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

$cameralife->getFeature('security');
$cameralife->getFeature('filestore');
$cameralife->theme->Header($cameralife);

$_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 'rand';
list($sort,$type) = explode('-', $_GET['page'].'-');
$search = new Search('');
$search->setPage(0, 11);
$search->setSort($sort); # rand, newest, newest-folders, unpopular
$counts = $search->getCounts();

if ($type == 'folders')
    $results = $search->getFolders();
else
    $results = $search->getPhotos();

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
  $resultOpenGraph = $result->GetOpenGraph();
  echo '<div class="l1" style="-moz-transform:rotate('.rand(-10,10).'deg); -webkit-transform:rotate('.rand(-10,10).'deg)">';
  echo '<a href="'.htmlspecialchars($resultOpenGraph['og:url']).'" class="l2">';
  echo '<img alt="'.htmlspecialchars($resultOpenGraph['og:title']).'" src="'.htmlspecialchars($resultOpenGraph['og:image']).'" class="l3">';
  if (isset($resultOpenGraph['og:image:width']) && isset($resultOpenGraph['og:image:height']))
      echo '<div class="l4" style="width:'.($resultOpenGraph['og:image:width']).'px">'.htmlentities($resultOpenGraph['og:title']).'</div>';
  else
      echo '<div class="l4">'.htmlentities($resultOpenGraph['og:title']).'</div>';
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
        $rootOpenGraph = $root->getOpenGraph();
        $root->setSort('newest');
        $folders = $root->getDescendants($cameralife->theme->getPref('main_folders_n'));

        foreach ($folders as $folder)
        {
          $folderOpenGraph = $folder->GetOpenGraph();
          echo "<tr><td><h3><a href=\"".htmlspecialchars($folderOpenGraph['og:url'])."\"> ";
          echo htmlentities($folderOpenGraph['og:title'])."</a></h3>\n";

          $folder->SetSort('rand');
          $folder->SetPage(0, 11);
          $photos = $folder->GetPhotos();
          echo '<div style="height:80px" class="clipbox">';
          foreach($photos as $result)
          {
            $photoOpenGraph = $result->GetOpenGraph();
            echo '<div class="l1" style="-moz-transform:rotate('.rand(-10,10).'deg); -webkit-transform:rotate('.rand(-10,10).'deg);">';
            echo '<a class="minipolaroid" href="'.htmlspecialchars($photoOpenGraph['og:url']).'">';
            echo '<img width="'.intval($photoOpenGraph['og:image:width']/2).'" src="'.htmlspecialchars($photoOpenGraph['og:image']).'" alt="'.htmlentities($photoOpenGraph['og:title']).'" />';
            echo '</a>';
            echo '</div>';
          }
          echo "</div>\n";
        }
        echo "<tr><td><h3><a href=\"".htmlspecialchars($rootOpenGraph['og:url'])."\">... show all folders</a></h3>";
?>
    </table>
  </div>
  <div class="col-sm-6">
    <h2>Photo Albums</h2>
    <table class="table">
<?php

        foreach (Topic::getTopics() as $topic)
        {
          $openGraph = $topic->GetOpenGraph();
          echo "<tr><td><h3><a href=\"".htmlentities($openGraph['og:url'])."\">";
          echo $cameralife->theme->Image('small-topic');
          echo htmlentities($openGraph['og:title'])."</a></h3>\n";

          if ($cameralife->theme->getPref('main_topics')==2) // Link a couple albums
          {
            $topic->SetSort('rand');
            $topic->SetPage(0, 4);

            echo '<div style=" overflow: hidden; white-space: nowrap; text-overflow: ellipsis; ">';
            $count = 0;
            foreach($topic->GetAlbums() as $album)
            {
              if ($count++) echo ", ";
              $openGraphAlbum = $album->GetOpenGraph();
              echo "<a href=\"".htmlentities($openGraphAlbum['og:url'])."\">";
              echo htmlentities($openGraphAlbum['og:title'])."</a>\n";
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
  $cameralife->theme->Footer();
?>
