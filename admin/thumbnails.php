<?php
/** 
 * An administrative tool that updates thumbnails
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @version 2.6.3b6
 * @access public
 * @todo Uses non-public database access
 */

  @ini_set('max_execution_time',9000);
  $features=array('database','theme','security','imageprocessing', 'photostore');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);
  chdir ($cameralife->base_dir);

  $cameralife->Security->authorize('admin_file', 1); // Require
  $home = $cameralife->GetIcon('small');

  $lastdone = (int) $_GET['lastdone']
    or $lastdone = -1;
  $starttime = (int) $_GET['starttime']
    or $starttime = time();
  $numdone = (int) $_GET['numdone']
    or $numdone = 0;
?>
<html>
<head>
  <title><?= $cameralife->preferences['core']['sitename'] ?></title>
  <link rel="stylesheet" href="admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body>

<div id="header">
  <h1>Site Administration &ndash; Thumbnailer</h1>
  <a href="<?= $home['href'] ?>"><img src="<?= $cameralife->IconURL('small-main')?>"><?= $home['name'] ?></a>
  <a href="index.php"><img src="<?= $cameralife->IconURL('small-admin')?>">Site Administration</a>
</div>


<form id="form1" method="get">

<?php
  $total = $cameralife->Database->SelectOne('photos', 'count(*)');
  $done = $cameralife->Database->SelectOne('photos', 'count(*)', "id <= $lastdone");
  $todo = $cameralife->Database->SelectOne('photos', 'count(*)', "id > $lastdone");
  $timeleft = ceil((time()-$starttime) * $todo / ($numdone + $done/500 + 1) / 60);

  echo "<p>We are now caching thumbnails. This avoids a delay when a photo is viewed for the first time.</p>\n";
  echo "<h3>Progress: $done of $total done";
  if ($done != $total)
    echo " (about $timeleft minutes left)";
  echo "</h3>\n";
  echo "<p><div style='width: 500px; background: #fff; border: 1px solid black; padding: 2px; margin:2em auto'>";
  echo "<div style='height: 25px; background: #347 url(".$cameralife->IconURL('progress').") repeat-x; width:".($done/$total*100)."%'></div>";
  echo "</div></p>\n";
  flush();

  $next1000 = $cameralife->Database->Select('photos', 'id', "id > $lastdone", 'ORDER BY id LIMIT 500');
  $fixed = 0;

  while(($next = $next1000->FetchAssoc()) && ($fixed < 10))
  {
    $curphoto = new Photo($next['id']);
    if ($cameralife->PhotoStore->CheckThumbnails($curphoto))
    {
      echo "<div>Updated #".$next['id']."</div>\n";
      flush();
      $fixed++;
    }
    $curphoto->Destroy();
    $lastdone = $next['id'];
  }

  $numdone += $fixed;
  if ($todo > 0)
  {
    echo "<script language='javascript'>window.setTimeout('window.location=\"thumbnails.php?lastdone=$lastdone&starttime=$starttime&numdone=$numdone\"',1000)</script>\n" ;
    echo "<p><a href=\"thumbnails.php?lastdone=$lastdone&starttime=$starttime&numdone=$numdone\">Click here to continue</a> if the Javascript redirect doesn't work.</p>\n";
  }
?>

</body>
</html>
