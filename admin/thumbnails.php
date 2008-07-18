<?php
  # Admin tool to update thumbnails since they are lazily created

  @ini_set('max_execution_time',9000);
  $features=array('database','theme','security','imageprocessing', 'photostore');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);
  chdir ($cameralife->base_dir);

  $cameralife->Security->authorize('admin_file', 1); // Require

  $lastdone = $_GET['lastdone'] 
    or $lastdone = -1;
  $starttime = $_GET['starttime']
    or $starttime = time();
  $numdone = $_GET['numdone']
    or $numdone = 0;
?>
<html>
<head>
  <title><?= $cameralife->preferences['core']['sitename'] ?></title>
  <link rel="stylesheet" href="admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
  <script language="javascript">
    function changeall() {
      val = document.getElementById('status').value;
      inputs = document.getElementsByTagName('select');
      for (var i = 0; i < inputs.length; i++) {
          inputs[i].value=val;
      }
    }
  </script>
</head>
<body>

<div id="header">
<h1>Site Administration &ndash; Thumbnailer</h1>
<?php
  $home = $cameralife->GetIcon('small');
  echo '<a href="'.$home['href']."\"><img src=\"".$cameralife->IconURL('small-main')."\">".$home['name']."</a>\n";
?> |
<a href="index.php"><img src="<?= $cameralife->IconURL('small-admin')?>">Site Administration</a>
</div>


<form id="form1" method="get">

<?php
  $total = $cameralife->Database->SelectOne('photos', 'count(*)');
  $done = $cameralife->Database->SelectOne('photos', 'count(*)', "id <= $lastdone");
  $todo = $cameralife->Database->SelectOne('photos', 'count(*)', "id > $lastdone");
  $timeleft = round((time()-$starttime) * $todo / ($numdone + $done/500 + 1) / 60, 0);

  echo '<p>We are now caching thumbnails. This avoids a delay when a photo is viewed for the first time.</p>';
  echo "<h3>Progress: $done of $total done";
  if ($done != $total)
    echo " (about $timeleft minutes left)";
  echo "</h3>\n";
  echo "<p><div style='width: 500px; background: #fff; border: 1px solid black; padding: 2px; margin:2em auto'>";
  echo "<div style='height: 25px; background: #347 url(".$cameralife->IconURL('progress').") repeat-x; width:".($done/$total*100)."%'></div>";
  echo "</div></p>\n";
  if ($todo == 0) die();

  $next1000 = $cameralife->Database->Select('photos', 'id', "id > $lastdone", 'ORDER BY id LIMIT 500');
  $fixed = 0;
  
  while(($next = $next1000->FetchAssoc()) && ($fixed < 10))
  {
    $curphoto = new Photo($next['id']);
    flush();
    if ($cameralife->PhotoStore->CheckThumbnails($curphoto))
    {
      echo "Updating #".$next['id']."<br>\n";
      $fixed++;
    }
    $curphoto->Destroy();
    $lastdone = $next['id'];
  }

  $numdone += $fixed;
  //echo "Fixed: $fixed photos.<br>\n";
  //echo "Continue: <a href='?lastdone=$lastdone'>Continue</a><br>\n";
  echo "<script language='javascript'>window.location='thumbnails.php?lastdone=$lastdone&starttime=$starttime&numdone=$numdone'</script>" ;
?>

</body>
</html>
