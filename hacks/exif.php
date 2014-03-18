<?php

  // Recaches EXIF data

  @ini_set('max_execution_time',9000);
  $features=array('theme','security','imageprocessing', 'photostore');
  require '../main.inc';
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
  <link rel="stylesheet" href="../admin/admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body>

<div id="header">
  <h1>Hacks &ndash; EXIF recache</h1>
  <a href="<?= $home['href'] ?>"><img src="<?= $cameralife->IconURL('small-main')?>"><?= $home['name'] ?></a>
  <a href="index.php"><img src="<?= $cameralife->IconURL('small-admin')?>">Site Administration</a>
</div>

<?php
  flush();
  $total = $cameralife->Database->SelectOne('photos', 'count(*)');
  $done = $cameralife->Database->SelectOne('photos', 'count(*)', "id <= $lastdone");
  $todo = $cameralife->Database->SelectOne('photos', 'count(*)', "id > $lastdone");
  $timeleft = ceil((time()-$starttime) * $todo / ($numdone + $done/500 + 1) / 60);

  echo "<p>Reloads EXIF data for all your photos. Photos with the rotation flag which have not been edited are rotated.</p>\n";
  echo "<h3>Progress: $done of $total done";
  if ($done != $total)
    echo " (about $timeleft minutes left)";
  echo "</h3>\n";
  echo "<p><div style='width: 500px; background: #fff; border: 1px solid black; padding: 2px; margin:2em auto'>";
  echo "<div style='height: 25px; background: #347 url(".$cameralife->IconURL('progress').") repeat-x; width:".($done/$total*100)."%'></div>";
  echo "</div></p>\n";
  flush();

  $next1000 = $cameralife->Database->Select('photos', 'id', "id > $lastdone", 'ORDER BY id LIMIT 100');
  $fixed = 0;

  while (($next = $next1000->FetchAssoc()) && ($fixed < 30)) {
    $curphoto = new Photo($next['id']);

    if ($curphoto->Get('modified') == NULL || $curphoto->Get('modified') == 0) {
/// UPDATE THIS LINE TO SKIP EXISTING EXIF DATA
      if (count($curphoto->GetEXIF()) && 0) {
        echo "Skipped #".$next['id']."<br>\n";
      } else {
        $curphoto->LoadImage(/*onlyWantEXIF=*/true);

        $EXIF = $curphoto->GetEXIF();
        if ($EXIF['Orientation'] == 3) {
          $cameralife->PhotoStore->ModifyFile($curphoto, NULL);
          echo "Flagged #".$next['id']." for rotation<br>\n";
        } elseif ($EXIF['Orientation'] == 6) {
          $cameralife->PhotoStore->ModifyFile($curphoto, NULL);
          echo "Flagged #".$next['id']." for rotation<br>\n";
        } elseif ($EXIF['Orientation'] == 8) {
          $cameralife->PhotoStore->ModifyFile($curphoto, NULL);
          echo "Flagged #".$next['id']." for rotation<br>\n";
        } else {
          echo "Updated #".$next['id']."<br>\n";
        }
      }
      flush();
      $fixed++;
    }
    $curphoto->Destroy();
    $lastdone = $next['id'];
  }

  $numdone += $fixed;
  if ($todo > 0) {
    echo "<script language='javascript'>window.setTimeout('window.location=\"exif.php?lastdone=$lastdone&starttime=$starttime&numdone=$numdone\"',500)</script>\n" ;
    echo "<p><a href=\"exif.php?lastdone=$lastdone&starttime=$starttime&numdone=$numdone\">Click here to continue</a> if the Javascript redirect doesn't work.</p>\n";
  } else
    echo "<p>All photos updated. Now you can <a href=\"../admin/thumbnails.php\">update thumbnail caches</a>.</p>\n"
?>

</body>
</html>
