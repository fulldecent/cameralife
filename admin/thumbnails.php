<?php
  # Admin tool to update thumbnails since they are lazily created

  @ini_set('max_execution_time',9000);
  $features=array('database','theme','security','imageprocessing');
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
  <title><?= $cameralife->preferences['core']['sitename'] ?> - Cache updater</title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
</head>
<body>


<form id="form1" method="get">

<?php
  $menu = array();
  $menu[] = array("name"=>$cameralife->preferences['core']['siteabbr'],
                  "href"=>"../index.php",
                  'image'=>'small-main');
  $menu[] = array("name"=>"Administration",
                  "href"=>"../admin/index.php",
                  'image'=>'small-admin');

  $cameralife->Theme->TitleBar("Cache Updater",
                               'admin',
                               "Update thumbnails for photos all at once",
                               $menu);

  $total = $cameralife->Database->SelectOne('photos', 'count(*)');
  $done = $cameralife->Database->SelectOne('photos', 'count(*)', "id <= $lastdone");
  $todo = $cameralife->Database->SelectOne('photos', 'count(*)', "id > $lastdone");
  $timeleft = round((time()-$starttime) * $todo / ($numdone + $done/500 + 1) / 60, 0);

  echo 'We are now caching thumbnails. Thumbnails are also cached the first time any user views a photo. Caching ithem now avoids that small delay for the user. This process is not necessary if you are impatient.';
  echo "<h3>Progress: $done of $total done (about $timeleft minutes left)</h3>\n";
  echo "<div style='width: 500px; background: #fff; border: 1px solid black; padding: 2px; margin:2em'>";
  echo "<div style='height: 25px; background: #347 url(".$cameralife->Theme->ImageURL('progress').") repeat-x; width:".($done/$total*100)."%'></div>";
  echo "</div>\n";
  if ($todo == 0) die();

  $next1000 = $cameralife->Database->Select('photos', 'id', "id > $lastdone", 'ORDER BY id LIMIT 500');
  $fixed = 0;
  
  while(($next = $next1000->FetchAssoc()) && ($fixed < 10))
  {
    $curphoto = new Photo($next['id']);
    flush();
    if ($curphoto->CheckThumbnail())
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
