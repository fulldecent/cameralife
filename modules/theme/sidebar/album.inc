<?php
  /**
  *Editing an album
  *
  *<b>Note</b> $cameralife and $album are set by ShowPage()
  *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  *@link http://fdcl.sourceforge.net
  */
  /**
  */
  # $cameralife and $album are set by ShowPage()

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');

  $album->Set('hits', $album->Get('hits') + 1);
  $results = $album->GetPhotos();
  $counts = $album->GetCounts();
  $icon = $album->GetIcon();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?= $cameralife->GetPref('sitename').': '.$album->Get('name')?></title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
  <link rel="alternate" type="application/rss+xml" title="RSS feed of <?= $album->Get('name') ?>" href="rss.php&#63;q=<?= $album->Get('term') ?>"/>
</head>
<body>

<?php
  $menu = array();
  $menu[] = $cameralife->GetIcon('small');
  $menu[] = $album->GetTopic()->GetIcon('small');

  if ($cameralife->Security->authorize('admin_albums') && !$_GET['edit'])
  {
    $href = AddParam($icon['href'], 'edit', 'true');

    $menu[] = array('name'=>'Edit this Album',
                    "href"=>$href,
                    'image'=>'small-admin');
  }

  $href = AddParam($icon['href'], 'help');

  $menu[] = array('name'=>'Add photos to this Album',
                  'href'=>$href,
                  'onclick'=>"javascript:void(document.getElementById('help').style.display='');return false",
                  'image'=>'small-admin');

  $cameralife->Theme->TitleBar($album->Get('name'),
                               'album',
                               '('.$counts['photos'].' total photos)',
                               $menu);

  if ($_GET['edit'] && $cameralife->Security->authorize('admin_albums'))
  {
?>
    <div class="administrative" align=center>
    <form name="form1" method=post action="<?= $cameralife->base_url ?>/album_controller.php">
    <input type="hidden" name="id" value="<?= $album->Get('id') ?>">
    <input type="hidden" name="target" value="<?= $cameralife->base_url.'/album.php&#63;id='.$album->Get('id') ?>">
      <p class="info" align=left>
        You are editing the Album named <b><?= $album->Get('name') ?></b>, which is a collection of photos 
        whose descriptions contain "<b><?= $album->Get('term') ?></b>".
        <?php if (file_exists('setup/albums.html')) echo "<a href=\"setup/albums.html\">Click here for more information about albums</a>"; ?>
      </p>
      <table>
        <tr>
          <td>
            The Album's name:<td><input type="text" name="param1" value="<?= $album->Get('name') ?>"><br>
        <tr>
          <td>
            This album and others like it are:<td>
            <select name="param2">
            <?php 
              foreach(Topic::GetTopics() as $thetopic)
              {
                  $selected = ($album->Get('topic') == $thetopic) ? 'selected' : '';
                  if ($album->Get('topic') == $thetopic) $preselect = true;
                  echo "<option $selected onclick=\"javascript:document.form1.topicother.disabled=true\" value=\"$thetopic\">$thetopic</option>\n";
              }
            ?>
            <option value="othertopic" <?= $preselect ? '' : 'selected' ?> onclick="javascript:document.form1.topicother.disabled=false">(A new topic)</option>
            </select>
            <input <?= $preselect ? 'disabled' : '' ?> type="text" name="param3" name="topicother" id="topicother" value="NEW TOPIC">
        <tr>
          <td class="info">(Deleting this album will not delete the photos)
          <td>
            <input type=submit name="action" value="Update">
            <input type=submit name="action" value="Delete">
            <a href="<?= $icon['href'] ?>">(cancel)</a>
      </table>
    </form>
    </div>
<?php
  }
  if ($_GET['poster_id'])
  {
    $cameralife->Security->authorize('admin_albums',1);
    $album->SetPoster($_GET['poster_id']);
?>
    <div class="administrative" align=center>
      The album <b><?= $album->Get('name') ?></b> will be represented by this photo 
      <img src="<?= $album->GetPoster()->GetMedia() ?>" alt="Poster photo">
    </div>
<?php
  }
?>
  <div id="help" class="administrative" style="display:<?php echo isset($_GET['help'])?'block':'none' ?>; text-align:center">
    To add photos to this album, edit any photo on the site and add "<b><?= $album->Get('term') ?></b>" to its description.
  </div>
<?php 
  $cameralife->Theme->Grid($results); 

  $cameralife->Theme->PageSelector(($sort == 'rand()')?-1:$_GET['start'],$counts['photos'],12,"id=".$album->Get('id')); 
?>

<form method="POST" action="">
<p>
  Sort by <select name="sort">
<?php
    $options = $album->SortOptions();
    foreach($options as $option)
      echo "    <option ".$option[2]." value=\"".$option[0]."\">".$option[1]."</option>\n";
?>
  </select>
  <input type=submit value="Sort">
</p>
</form>

</form>
</body>
</html>

