<?php
  $features=array('database','security','theme', 'photostore');
  require "main.inc";

  $album = new Album($_GET['id']);
  $album->Set('hits', $album->Get('hits') + 1);
  $results = $album->GetPhotos();
  $counts = $album->GetCounts();
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
    $menu[] = array('name'=>'Edit this Album',
                    "href"=>"$PHPSELF&#63;id=".$album->Get('id').'&amp;edit=true',
                    'image'=>'small-admin');
  }

  $menu[] = array('name'=>'Add photos to this Album',
                  'href'=>"$PHPSELF&#63;id=".$album->Get('id').'&amp;help',
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
    <form name="form1" method=post action="album_controller.php">
    <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
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
              $result = $cameralife->Database->Select('albums','DISTINCT topic');
              while ($topics = $result->FetchAssoc())
              {
                  $topic = $topics['topic'];
                  $selected = ($album->Get('topic') == $topic) ? 'selected' : '';
                  if ($album->Get('topic') == $topic) $preselect = true;
                  echo "<option $selected onclick=\"javascript:document.form1.topicother.disabled=true\" value=\"$topic\">$topic</option>\n";
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
            <a href="<?= $_SERVER['PHP_SELF'] ?>&#63;id=<?= $album->Get('id') ?>">(cancel)</a>
      </table>
    </form>
    </div>
<?php
  }
  if ($_GET['poster_id'])
  {
    $cameralife->Security->authorize('admin_albums',1);
?>
    <div class="administrative" align=center>
            The album <b><?= $album->Get('name') ?></b> will be represented by this photo 
<?php
    $cameralife->Database->SelectOne('photos','COUNT(*)','id='.$_GET['poster_id'])
      or $cameralife->Error('The selected poster photo does not exist', __FILE__, __LINE__);
    $album->Set('poster_id', $_GET['poster_id']);

    echo '<img src="'.$album->GetPoster()->GetMedia().'" alt="Poster photo">';
    echo '</div>';
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

