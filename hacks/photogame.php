<?php
# A game to see which photos are good or suck

# mysql
# CREATE TABLE likebetter (id INT, chosen BOOL, addr INT UNSIGNED, KEY(id), UNIQUE(id, addr, chosen));

$features=array('theme','security','imageprocessing','filestore');
require '../main.inc';
$cameralife->base_url = dirname($cameralife->base_url);
chdir ($cameralife->base_dir);

$search = new Search('');
$search->SetSort('rand');
$search->SetPage(0, 4);
$photos = $search->GetPhotos();

if (isset($_GET['win'])) {
  $cameralife->Database->Insert('likebetter', array('id'=>(int) $_GET['win'],'chosen'=>1,'addr'=>sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']))), 'ON DUPLICATE KEY UPDATE chosen=1');
  foreach (explode(',', $_GET['lose']) as $loser) {
    if ((int) $loser != $_GET['win']) {
      $cameralife->Database->Insert('likebetter', array('id'=>(int) $loser,'chosen'=>0,'addr'=>sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']))), 'ON DUPLICATE KEY UPDATE chosen=0');
    }
  }
}

$score = $cameralife->Database->SelectOne('likebetter', 'COUNT(*)', 'addr='.sprintf("%u", ip2long($_SERVER['REMOTE_ADDR'])));

?>
<!DOCTYPE html>
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?> - Photo Game</title>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
  <style>
h1{font-family:sans-serif}
img{width:23%;display:inline-block;padding:5px}
  </style>
</head>
<body>
  <h1><span style="font-weight: normal"><?= $cameralife->GetPref('sitename') ?></span> Photo game</h1>
  <p>Click the best photo to win. Your score is <?= $score ?>. For the main site, <a href="../index.php">click here</a>.</p>
  <?php
    $numbers;
    foreach ($photos as $photo) {$numbers[] = $photo->Get('id');}
    foreach ($photos as $photo) {
      $src = $photo->GetMedia('scaled');
      $href = '?win='.$photo->Get('id').'&amp;lose='.implode(',',$numbers);
      echo "<a href=\"$href\"><img alt=\"pick me\" src=\"$src\"></a>\n";
    }

  ?>
</body>
</html>
