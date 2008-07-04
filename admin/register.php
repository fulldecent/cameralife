<?php
  # Allow you to submit information about your site to me

  $features=array('database','security');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);

  $cameralife->Security->authorize('admin_customize', 1);
  $stats = new Stats;
  $counts = $stats->GetCounts();
?>
<html>
<head>
  <title><?= $cameralife->preferences['core']['sitename'] ?></title>
  <link rel="stylesheet" href="admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body>

<div id="header">
<h1>Site Administration &ndash; Register</h1>
<?php
  $home = $cameralife->GetSmallIcon();
  echo '<a href="'.$home['href']."\"><img src=\"".$cameralife->IconURL('small-main')."\">".$home['name']."</a>\n";
?> |
<a href="index.php"><img src="<?= $cameralife->IconURL('small-admin')?>">Site Administration</a>
</div>

<p>
  We would appreciate if you mail this to cameralife@phor.net with 
  the subject CAMERALIFE-FEEDBACK including any of your feedback.
</p>

<p style="border:3px solid brown; background: tan; color: black; padding:20px">
  From: <?= $cameralife->preferences['core']['owner_email'] ?><br>
  To: cameralife@phor.net<br>
  Subj: CAMERALIFE-FEEDBACK<br>
  <br>
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


