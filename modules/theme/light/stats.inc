<?php
/**Displays popular photos and albums,and fun facts such as the amount of time a site has been online
 *
 * <b> Note </b> $cameralife and $album are set by ShowPage()
 *@version 2.6.3b4
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 *@link http://fdcl.sourceforge.net
 */

  # $cameralife and $album are set by ShowPage()
  $cameralife->GetFeature('photostore');

  $stats = new Stats;
  $counts = $stats->GetCounts();
  $popular_photos = $stats->GetPopularPhotos();
  $popular_albums = $stats->GetPopularAlbums();
  $funfacts = $stats->GetFunFacts();

  $header = '
  <style type="text/css">
    div.bar {margin: 0; background: red; float: left; height: 1em; clear: both}
  </style>
';
  $cameralife->Theme->Header($cameralife->GetPref('sitename').' - Stats', 'stats');
?>
<div id="sidebar">
  <h1>Most popular photos</h1>
<?php
  foreach ($popular_photos as $result)
  {
    if ($i++==3) break;
    $icon = $result->GetIcon();
    echo '<div class="item"><a href="'.$icon['href'].'"><img src="'.$icon['image'].'" alt="'.$icon['name'].'" /></a>';
    echo '<a style="clear: left" href="'.$icon['href'].'">'.$result->Get('hits').' hits</a><span class="description">'.$icon['name'].'</span></div>';
  }
?>
</div>
<div id="text">

  <h1>Most popular photos</h1>
  <ol>
    <?php
      foreach ($popular_photos as $photo)
      {
        $percent = $photo->Get('hits') * 100 / $popular_photos[0]->Get('hits');
        echo "<li><div class='bar' style='width: ${percent}px; margin-right:".(20+100-$percent)."px'></div>";
        echo "<a href=\"photo.php&#63;id=".$photo->Get('id').'">&quot;'.$photo->Get('description')."&quot;</a>\n";
        echo "<font size=\"-2\">(".$photo->Get('hits').")</font>\n";
      }
    ?>
  </ol>
  <ul>
    <li>There are <?= number_format($counts['photos']) ?> photos with <?= number_format($counts['photohits']) ?> hits
    <li>They contain <?= number_format($counts['pixels']) ?> total pixels
  </ul>

  <h1>Most popular albums</h1>
  <ol>
    <?php
      foreach ($popular_albums as $album)
      {
        $percent = $album->Get('hits') * 100 / $popular_albums[0]->Get('hits');
        echo "<li><div class='bar' style='width: ${percent}px; margin-right:".(20+100-$percent)."px'></div>";
        echo "<a href=\"album.php&#63;id=".$album->Get('id').'">&quot;'.$album->Get('name')."&quot;</a>\n";
        echo "<font size=\"-2\">(".$album->Get('hits').")</font>\n";
      }
    ?>
  </ol>
  <ul>
    <li>There are <?= number_format($counts['albums']) ?> albums with <?= number_format($counts['albumhits']) ?> hits
    <li>They are in <?= $counts['topics'] ?> topics
  </ul>

  <h1>Fun facts</h1>
  <ul>
    <li><?= $cameralife->GetPref('sitename') ?> has been online since <?= $cameralife->GetPref('sitedate') ?> (<b><?= $counts['daysonline'] ?> days</b>).
<?php
  shuffle($funfacts);
  for ($i = 0; $i < 3; $i++)
    echo '<li>'.$funfacts[$i]."</li>\n";
?>
    <li><a href="&#63;">Click here to reload more random facts.</a></li>
  </ul>

<?php
  $cameralife->Theme->Footer();
?>
