<?php
/**Photo Restore -
*This tool will restore your backed up photos.
*<b>Note:Use absolute paths </b>
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
*/

/**
*/
  @ini_set('max_execution_time',9000);

  $features=array('database','security','imageprocessing', 'photostore');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);

  $cameralife->Security->authorize('admin_file', 1); // Require

  function mkdir_recursive($pathname, $mode=0777)
  {
    is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname), $mode);
    return is_dir($pathname) || @mkdir($pathname, $mode);
  }

  $lastdone = $_GET['lastdone']
    or $lastdone = -1;
  $starttime = $_GET['starttime']
    or $starttime = time();
  $numdone = $_GET['numdone']
    or $numdone = 0;
?>
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <link rel="stylesheet" href="../admin/admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body>

<div id="header">
<h1>Hacks &ndash; Photo Restore</h1>
<?php
  $home = $cameralife->GetIcon('small');
  echo '<a href="'.$home['href']."\"><img src=\"".$cameralife->IconURL('small-main')."\">".$home['name']."</a>\n";
?> |
<a href="../admin/index.php"><img src="<?= $cameralife->IconURL('small-admin')?>">Site Administration</a>
</div>

<?php

  if ($_REQUEST['photodir'])
  {
    $photos = $cameralife->Database->Select('photos','id,filename,path,modified',"id>$lastdone",'ORDER BY path, filename');

    $total = $cameralife->Database->SelectOne('photos', 'count(*)');
    $done = $cameralife->Database->SelectOne('photos', 'count(*)', "id <= $lastdone");
    $todo = $cameralife->Database->SelectOne('photos', 'count(*)', "id > $lastdone");
    $timeleft = round((time()-$starttime) * $todo / ($numdone + $done/500 + 1) / 60, 0);

    echo "<h3>Progress: $done of $total done";
    if ($done != $total)
      echo " (about $timeleft minutes left)";
    echo "</h3>\n";
    echo "<p><div style='width: 500px; background: #fff; border: 1px solid black; padding: 2px; margin:2em auto'>";
    echo "<div style='height: 25px; background: #347 url(".$cameralife->IconURL('progress').") repeat-x; width:".($done/$total*100)."%'></div>";
    echo "</div></p>\n";

    if ($todo)
    {
      mkdir_recursive($_REQUEST['moddir']);

      while ($photo = $photos->FetchAssoc())
      {

        if($photo['modified'])
        {
//TODO fix hardcoded file format
// this first one could be /dev/null?
          $cameralife->PhotoStore->ModifyFile(new Photo($photo['id']), $_REQUEST['moddir'] . '/' . $photo['id'] . '_mod.jpg');
        }

        $ticket = $cameralife->PhotoStore->PutFile(new Photo($photo['id']), $_REQUEST['photodir'] . '/' . $photo['path'] . $photo['filename']);

//TODO doing this twice is not efficient!
        if($photo['modified'])
        {
//TODO fix hardcoded file format
          $cameralife->PhotoStore->ModifyFile(new Photo($photo['id']), $_REQUEST['moddir'] . '/' . $photo['id'] . '_mod.jpg');
        }

        echo "<p>Copying: " . $photo['path'] . $photo['filename'] . "</p>\n";
        flush();

        $lastdone = $photo['id'];
        if (++$i == 10)
          break;
      }
      echo "<script language='javascript'>window.location='restore.php?photodir=".urlencode($_REQUEST['photodir'])."&moddir=".urlencode($_REQUEST['moddir'])."&starttime=$starttime&lastdone=$lastdone'</script>";
    }
  }
  else
  {
?>

<p>This tool will restore your backed up photos. <strong>USE ABSOLUTE PATHS</strong>.</p>

<?php
  if ($cameralife->GetPref('photostore') == 'flickr' && !is_writable(dirname(__FILE__)))
  {
    echo '<div style="background: red">I need to write into '.dirname(__FILE__).' please make it writable.</div>';
  }
?>

<form method="post" action="http://<?= $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'] ?>">

<table>
  <tr>
    <td>Restore photo dir: <td><input type="text" name="photodir" style="width: 200px" value="<?= $_REQUEST['photodir'] ? $_REQUEST['photodir'] : '/tmp/myphotos' ?>">
  <tr>
    <td>Restore modified dir: <td><input type="text" name="moddir" style="width: 200px" value="<?= $_REQUEST['moddir'] ? $_REQUEST['moddir'] : '/tmp/mymods' ?>">
  <tr>
    <td><td><input type="submit" value="Restore">
</table>

  </form>

<?php
  }
?>
</body>
</html>

