<?php
/**Displays and sorts albums
*
*<b>Note</b> $cameralife and $topic are set by ShowPage()
  *@link http://fdcl.sourceforge.net
  *@version 2.6.3
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */
  /**
  */
  # $cameralife and $topic are set by ShowPage()

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');

  $albums = $topic->GetAlbums();
  $counts = $topic->GetCounts();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?= $cameralife->GetPref('sitename').' : '.$topic->GetName() ?></title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
    <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
  <meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=yes;" />
</head>
<body>
<form name="form1" method=post action="album_controller.php">
<h1><?= $topic->GetName() ?></h1>
<?php
  $cameralife->Theme->Grid($albums);
  
  if ($sort == 'rand()') $start = -1;
  $cameralife->Theme->PageSelector($start,$total,12,"name=".urlencode($topic->GetName()));
?>
</form>

<form method="POST" action="">
<p>
  Sort by <select name="sort">
<?php
    $options = $topic->SortOptions();
    foreach($options as $option)
      echo "    <option ".$option[2]." value=\"".$option[0]."\">".$option[1]."</option>\n";
?>
  </select>
  <input type=submit value="Sort">
</p>
</form>


</body>
</html>
