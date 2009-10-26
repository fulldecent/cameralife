<?php
  # A game to see which photos are good or suck

  # mysql
  # CREATE TABLE likebetter (id INT, chosen BOOL, addr INT UNSIGNED, KEY(id), UNIQUE(id, addr, chosen));

  $features=array('database','theme','security','imageprocessing','photostore');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);
  chdir ($cameralife->base_dir);

  $search = new Search('');
  $search->SetSort('rand');
  $search->SetPage(0, 4);
  $photos = $search->GetPhotos();

  if ($_GET['win'])
  {
    $cameralife->Database->Insert('likebetter', array('id'=>(int) $_GET['win'],'chosen'=>1,'addr'=>sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']))), 'ON DUPLICATE KEY UPDATE chosen=1');
    foreach (explode(',', $_GET['lose']) as $loser)
    {
      if ((int) $loser != $_GET['win'])
      {
        $cameralife->Database->Insert('likebetter', array('id'=>(int) $loser,'chosen'=>0,'addr'=>sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']))), 'ON DUPLICATE KEY UPDATE chosen=0');
      }
    }
  }

  $score = $cameralife->Database->SelectOne('likebetter', 'COUNT(*)', 'addr='.sprintf("%u", ip2long($_SERVER['REMOTE_ADDR'])));

?>
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?> - Photo Game</title>
  <link rel="stylesheet" type="text/css" href="photogame.css">
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
</head>
<body>
<?php if(!$_GET['mini']) {?>
  <h1 style="font-family:sans-serif"><span style="font-weight: normal"><?= $cameralife->GetPref('sitename') ?></span> Photo game</h1>
<?php } ?>
  <p>Click the best photo to win. Your score is <?= $score ?>. For the main site, <a href="../index.php">click here</a>.</p>

  <div>
  <?php
    $numbers;
    foreach ($photos as $photo)
    {
      $numbers[] = $photo->Get('id');
    }
    foreach ($photos as $photo)
    {
      $src = $photo->GetMedia($_GET['mini']?'thumbnail':'scaled');
      $href = '?win='.$photo->Get('id').'&amp;lose='.implode(',',$numbers).($_GET['mini']?'&mini=yes':'');
      echo "<div><a href=\"$href\"><img alt=\"pick me\" src=\"$src\"></a></div>\n";
    }

  ?>
  </div>

</body>
</html>

