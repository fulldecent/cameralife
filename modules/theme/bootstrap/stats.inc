<?php
/**
 * Displays popular photos and albums,and fun facts such as the amount of time a site has been online
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

# $cameralife and $album are set by ShowPage()
$cameralife->GetFeature('photostore');

$stats = new Stats;
$counts = $stats->GetCounts();
$popular_photos = $stats->GetPopularPhotos();
$popular_albums = $stats->GetPopularAlbums();
$funfacts = $stats->GetFunFacts();

$cameralife->Theme->Header($cameralife->GetPref('sitename').' - Stats');
?>
      <h1>Site stats</h1>
      <ul class="thumbnails">
        <li class="span4">
          <div class="thumbnail">
            <h2>Most popular photos</h2>
            <table class="table table-condensed">
<?php
foreach ($popular_photos as $photo) {  
  $percent = $photo->Get('hits') * 100 / $popular_photos[0]->Get('hits');
  echo "<tr><td>";
  echo "<div style=\"width:50px; clear:left\" class=\"progress progress-info\"><div class=\"bar\" style=\"width: $percent%\"></div></div>";
  echo "<td>".number_format($photo->Get('hits'));
  echo "<td><a href=\"photo.php&#63;id=".$photo->Get('id').'">'.$photo->Get('description')."</a>\n";
}
?>
            </table>
          </div>
        </li>
        <li class="span4">
          <div class="thumbnail">
            <h2>Most popular albums</h2>
            <table class="table table-condensed">
<?php
foreach ($popular_albums as $photo) {  
  $percent = $photo->Get('hits') * 100 / $popular_photos[0]->Get('hits');
  echo "<tr><td>";
  echo "<div style=\"width:50px; clear:left\" class=\"progress progress-success\"><div class=\"bar\" style=\"width: $percent%\"></div></div>";
  echo "<td>".number_format($photo->Get('hits'));
  echo "<td><a href=\"album.php&#63;id=".$photo->Get('id').'">'.$photo->Get('name')."</a>\n";
}
?>
            </table>
          </div>
        </li>
        <li class="span4">
          <div class="thumbnail">
            <h2>Fun facts</h2>
              <ul>
                <li><?= $cameralife->GetPref('sitename') ?> has been online since <?= $cameralife->GetPref('sitedate') ?> (<b><?= $counts['daysonline'] ?> days</b>).
<?php
  shuffle($funfacts);
  for ($i = 0; $i < 3; $i++)
    echo '<li>'.$funfacts[$i]."</li>\n";
?>
                <li><a href="&#63;">Click here to reload more random facts.</a></li>
              </ul>
          </div>
        </li>
      </ul>

<?php
  $cameralife->Theme->Footer();
?>
