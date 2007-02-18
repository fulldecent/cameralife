<?php
  # Allow you to submit information about your site to me

  $features=array('database','theme','security');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);

  $cameralife->Security->authorize('admin_customize', 1);
  $stats = new Stats;
  $counts = $stats->GetCounts();
?>

<html>
<head>
  <title><?= $cameralife->preferences['core']['sitename'] ?> - Register</title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
</head>
<body>

<?php
  $menu = array();
  $menu[] = array("name"=>$cameralife->preferences['core']['siteabbr'],
                  "href"=>"../index.php",
                  'image'=>'small-main');
  $menu[] = array("name"=>"Administration",
                  "href"=>"index.php",
                  'image'=>'small-admin');


  $cameralife->Theme->TitleBar("Registration",
                               'admin',
                               "Provide feedback of your experiences with Camera Life",
                               $menu);
?>

<p align=left>
  You can copy this letter and email it to cameralife@phor.net with 
  the subject CAMERALIFE-FEEDBACK. We appreciate your feedback!
</p>

<p style="border:3px solid brown; background: tan; color: black; padding:20px">
  To the Camera Life team,<br>
  &nbsp;<br>
  I have set up a site named <strong><?= $cameralife->preferences['core']['sitename'] ?></strong> at <strong><?= str_replace('admin','',$cameralife->base_url) ?></strong> based on the <strong><?= $cameralife->version ?></strong> version of Camera Life.
  It has been up for <strong><?= $counts['daysonline'] ?></strong> days, contains <strong><?= $counts['photos'] ?></strong> photos and is running on <strong><?= eregi_replace('server.*','',eregi_replace('<[^>]*>', '', $_SERVER['SERVER_SIGNATURE'])) ?></strong>.<br>
  &nbsp;<br>
  You <strong>may/may not (pick one!)</strong> list my site on your Camera Life software demo page.<br>
  &nbsp;<br>
  I have some comments about the software, namely:<br>
  &nbsp;<br>
  <i><u><strong>comments</strong></u></i><br>
  &nbsp;<br>&nbsp;<br>
  Cheers,<br>
  &nbsp;<br>
  <strong>Your Name</strong><br>
  <?= $cameralife->preferences['core']['owner_email'] ?><br>
</p>
  
</body>
</html>


