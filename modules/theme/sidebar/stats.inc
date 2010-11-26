<?php
/**Displays popular photos and albums,and fun facts such as the amount of time a site has been online
*
* <b> Note </b> $cameralife and $album are set by ShowPage()
*@version 2.6.3b6
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  *@link http://fdcl.sourceforge.net
  */
/**
*/
  # $cameralife and $album are set by ShowPage()

  $stats = new Stats;
  $counts = $stats->GetCounts();
  $popular_photos = $stats->GetPopularPhotos();
  $popular_albums = $stats->GetPopularAlbums();
  $funfacts = $stats->GetFunFacts();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
  <style type="text/css">
    div.bar {margin: 0; background: red; float: left; height: 1em; clear: both}
  </style>
</head>
<body>

<?php
  $menu = array();
  $menu[] = $cameralife->GetIcon('small');
  $menu[] = array("name"=>"Powered by Camera Life",
                  "href"=>"http://fdcl.sourceforge.net/",
                  'image'=>'small-main');

  $cameralife->Theme->TitleBar("Site Statistics",
                               'stats',
                               FALSE,
                               $menu);

  if (count ($popular_photos) > 0) {
    $cameralife->Theme->Section('Most popular photos'); 
?>
  <ol>
    <?php
      foreach ($popular_photos as $photo)
      {
        $percent = $photo->Get('hits') * 100 / $popular_photos[0]->Get('hits');
        echo "<li><div class='bar' style='width: ${percent}px; margin-right:".(20+100-$percent)."px'></div>";
        echo "<a href=\"photo.php&#63;id=".$photo->Get('id').'">&quot;'.$photo->Get('description')."&quot;</a>\n";
        echo "<font size=\"-2\">(".$photo->Get('hits').")</font>\n";
      }
  }
    ?>
  </ol>
  <ul>
    <li>There are <?= number_format($counts['photos']) ?> photos with <?= number_format($counts['photohits']) ?> hits
    <li>They contain <?= number_format($counts['pixels']) ?> total pixels
  </ul>

<?php 
  if (count ($popular_albums) > 0) {
    $cameralife->Theme->Section('Most popular albums'); 
?>
  <ol>
    <?php
      foreach ($popular_albums as $album)
      {
        $percent = $album->Get('hits') * 100 / $popular_albums[0]->Get('hits');
        echo "<li><div class='bar' style='width: ${percent}px; margin-right:".(20+100-$percent)."px'></div>";
        echo "<a href=\"album.php&#63;id=".$album->Get('id').'">&quot;'.$album->Get('name')."&quot;</a>\n";
        echo "<font size=\"-2\">(".$album->Get('hits').")</font>\n";
      }
  }
    ?>
  </ol>
  <ul>
    <li>There are <?= number_format($counts['albums']) ?> albums with <?= number_format($counts['albumhits']) ?> hits
    <li>They are in <?= $counts['topics'] ?> topics
  </ul>

<?php $cameralife->Theme->Section('Fun Facts (reload for more)'); ?>
  <ul>
    <li><?= $cameralife->GetPref('sitename') ?> has been online since <?= $cameralife->GetPref('sitedate') ?> (<b><?= $counts['daysonline'] ?> days</b>).
  </ul>
  <ul>
<?php
  shuffle($funfacts);
  for ($i = 0; $i < 3; $i++)
  {
    echo '<li>'.$funfacts[$i]."\n";
  }
?>
  </ul>

</body>
</html>

