<?php
  /**
  *Editing an album
  *
  *<b>Note</b> $cameralife and $album are set by ShowPage()
  *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  *@link http://fdcl.sourceforge.net
  */
  /**
  */
  # $cameralife and $album are set by ShowPage()

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');

  $start = $_GET['start'] + 0;

  $album->Set('hits', $album->Get('hits') + 1);
  $results = $album->GetPhotos();
  $counts = $album->GetCounts();
  $icon = $album->GetIcon();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?= $cameralife->GetPref('sitename').': '.$album->Get('name')?></title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=yes;" />

  <link rel="alternate" type="application/rss+xml" title="RSS feed of <?= $album->Get('name') ?>" href="rss.php&#63;q=<?= $album->Get('term') ?>"/>
</head>
<body>
<h1><?= $album->Get('name') ?> album</h1>
<?php 
  $cameralife->Theme->Grid($results); 

  $cameralife->Theme->PageSelector(($sort == 'rand()')?-1:$_GET['start'],$counts['photos'],12,"id=".$album->Get('id')); 
?>

<form method="POST" action="">
<p>
  Sort by <select name="sort">
<?php
    $options = $album->SortOptions();
    foreach($options as $option)
      echo "    <option ".$option[2]." value=\"".$option[0]."\">".$option[1]."</option>\n";
?>
  </select>
  <input type=submit value="Sort">
</p>
</form>

</form>
</body>
</html>

