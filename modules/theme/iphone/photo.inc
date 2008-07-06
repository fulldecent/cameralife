<?php
  # $cameralife and $photo are set by ShowPage()

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');
  $cameralife->GetFeature('imageprocessing');

  # Display a photo, edit a photo, link to relevant places

  if ($_GET['referer'])
    $_SERVER['HTTP_REFERER'] = $_GET['referer'];
  $_SERVER['HTTP_REFERER'] = str_replace($cameralife->base_url,'', $_SERVER['HTTP_REFERER']);
  $_SERVER['HTTP_REFERER'] = preg_replace('|^/|', '', $_SERVER['HTTP_REFERER']);

  $sort = $_COOKIE["sort"] or $sort = 'id desc';

  if ($photo->Get('status') != 0) 
    $cameralife->Security->authorize('admin_file', 'This file has been flagged or marked private');
  $icon = $photo->GetIcon('small');
  if (strpos($icon['href'], '&#63;') !== FALSE)
    $hrefinquery = $icon['href'] . '&amp;';
  else
    $hrefinquery = $icon['href'] . '&#63;';

  if ($cameralife->Security->GetName())
    $rating = $avg = $cameralife->Database->SelectOne('ratings', 'AVG(rating)', 'id='.$photo->Get('id')." AND username='".$cameralife->Security->GetName()."'");
  else
    $rating = $avg = $cameralife->Database->SelectOne('ratings', 'AVG(rating)', 'id='.$photo->Get('id')." AND user_ip='".$_SERVER['REMOTE_ADDR']."'");

  $photo->Set('hits', $photo->Get('hits') + 1);

  $menu = array();
  $menu[] = $cameralife->GetIcon('small');

  // Show all the tasks the user can do (if they're allowed)
  if ($cameralife->Security->authorize('photo_rename'))
  {
    $menu[] = array('name'=>'Rename photo',
                    'href'=>$hrefinquery . 'action=rename&amp;referer='.urlencode($_SERVER['HTTP_REFERER']),
                    'image'=>'small-admin',
                    'section'=>'Tasks',
                    'onclick'=>"return toggleshowrename();");
  }
  if ($cameralife->Security->authorize('photo_delete'))
  {
    $menu[] = array('name'=>'Flag / Report photo',
                    'href'=>$hreyinquery . "id=".$photo->Get('id')."&amp;referer=".urlencode($_SERVER['HTTP_REFERER'])."&amp;action=delete",
                    'image'=>'small-admin',
                    'section'=>'Tasks',
                    'onclick'=>"document.getElementById('delete').style.display='';return false");
  }
  if ($cameralife->Security->authorize('photo_modify'))
  {
    $menu[] = array('name'=>'Modify (rotate, revert, ...)',
                    'href'=>$hrefinquery . "id=".$photo->Get('id')."&amp;referer=".urlencode($_SERVER['HTTP_REFERER'])."&amp;action=modify",
                    'image'=>'small-admin',
                    'section'=>'Tasks',
                    'onclick'=>"document.getElementById('modify').style.display='';return false");
  }
  if ($cameralife->Security->authorize('admin_albums') && (eregi ("album.php\?id=([0-9]+)",$_SERVER['HTTP_REFERER'],$regs) || eregi("albums/([0-9]+)",$_SERVER['HTTP_REFERER'],$regs)))
  {
    $album = new Album($regs[1]);
    $icon = $album->GetIcon('small');
#TODO a separate controller?
    if (strpos($icon['href'], '&#63;') !== FALSE)
      $icon['href'] .= '&poster_id='.$photo->Get('id');
    else 
      $icon['href'] .= '?poster_id='.$photo->Get('id');
    $icon['name'] = 'Use this for <b>'.$album->Get('name').'</b>';
    $icon['section'] = 'Tasks';
    $menu[] = $icon;
  }
  if ($photo->Get('status') == 0)
  {
    $icon = $photo->GetIcon();
    if (strpos($icon['href'], '&#63;') !== FALSE)
      $icon['href'] .= '&amp;referer='.urlencode($_SERVER['HTTP_REFERER'])."&amp;action=print";
    else
      $icon['href'] .= '&#63;referer='.urlencode($_SERVER['HTTP_REFERER'])."&amp;action=print";


    $menu[] = array('name'=>'Order prints',
                    'href'=>$icon['href'],
                    'image'=>'small-admin',
                    'section'=>'Tasks');
  }


  $context = $photo->GetRelated();
  foreach ($context as $icon)
  {
    $icon['section'] = 'Related Photos';
    $menu[] = $icon;
  }
  $photoPrev = $photo->GetPrevious();
  $photoNext = $photo->GetNext();


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?= htmlentities($photo->Get('description')) ?></title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=yes;" />
<link rel="stylesheet" type="text/css" href="delicious.css">
</head>
<body onLoad="init()">
<p><?= htmlentities($photo->Get('description')) ?> (<a href="<?= $hrefinquery . 'action=rename&amp;referer='.urlencode($_SERVER['HTTP_REFERER'])
 ?>">rename</a>)</p>
<?php

  if ($photo->Get('status') > 0)
  {
?>
    <div class="administrative" align=center>
      <p>This photo is not public</p>
    </div>
<?php
  }
  if ($cameralife->Security->authorize('photo_rename'))
  {
?>
    <form action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST" name="form" id="renameform">
    <input type="hidden" name="id" value="<?= $photo->Get('id') ?>">
    <input type="hidden" name="target" value="<?= $cameralife->base_url.'/photo.php&#63;id='.$photo->Get('id').'&referer='.urlencode($_SERVER['HTTP_REFERER']) ?>">
    <input type="hidden" name="action" value="rename">

    <div style="<?=($_GET['action'] == 'rename')?'':'display:none'?>" class="administrative" align=center id="rename">
      <table>
        <tr><td colspan=3><p>This <b>Title</b> will be shown on this page and on thumbnails of this photo elsewhere. <b>Keywords</b> will never be displayed. Searches and Albums both use the <b>Title</b> and <b>Keywords</b> to find photos.</p>
        <tr><td>Title:<td colspan=2><input id="formtitle" style="width: 100%;" type="text" name="param1" value="<?= htmlentities($photo->Get('description')) ?>">
      <?php
        $origname = ucwords($photo->Get('filename'));
        $origname = preg_replace('/.[^.]+$/', '', $origname);

        if (!eregi('^dscn', $origname) && !eregi('^im', $origname)) // useless filename
          echo '<tr><td><td><input type="button" onclick="javascript:set(\''.addslashes($origname).'\')" value="Set name to '.$origname.'">';
      ?>
        <tr><td>Keywords:
          <td style="width:100%"><input style="width: 100%;" id="tags" name="param2" value="<?= htmlentities($photo->Get('keywords')) ?>" autocomplete="off" type="text">
          <td>space&nbsp;separated
        <tr style="visibility: hidden; background:pink" id="suggestions">
          <td class="rs">completions
          <td><div id="suggest"></div>
        <tr><td>&nbsp;

        <tr><td><td>
          <input type="submit" value="Save Changes" onclick="makeRequest('<?= $cameralife->base_url ?>/photo_controller.php','renameform',renameDone);return false">
          <a href="<?= $icon['href'] ?>"
             onclick="return toggleshowrename()">(cancel)</a> 
      </table>
    </div>
    </form>
<?php
  }
  if ($cameralife->Security->authorize('photo_delete'))
  {
    if ($photoNext)
      $target = 'photo.php&#63;id='.$photoNext->Get('id');
    elseif ($photoPrev)
      $target = 'photo.php&#63;id='.$photoPrev->Get('id');
    elseif ($_SERVER['HTTP_REFERER'])
      $target = $_SERVER['HTTP_REFERER'];
    else
      $target = $cameralife->base_url.'/index.php';
?>
    <form action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST">
    <input type="hidden" name="id" value="<?= $photo->Get('id') ?>" />
    <input type="hidden" name="target" value="<?= $target ?>" />
    <input type="hidden" name="action" value="flag" />

    <div style="<?=($_GET['action'] == 'delete')?'':'display:none'?>"class="administrative" align=center id="delete">
      <p>Thank you for taking the time to report bad content. Please choose the obvious problem with this photo.</p>
      <table>
        <tr><td><input type="radio" name="param1" id="r1" value="indecent">
          <td><label for="r1">Indecent or Sensitive</label>
          <td>Obsenity, nudity
        <tr><Td><input type="radio" name="param1" id="r2" value="photography">
          <td><label for="r2">Bad photography</label>
          <td>Blurry, poor lighting, terrible cropping
        <tr><Td><input type="radio" name="param1" id="r3" value="subject">
          <td><label for="r3">Poor subject</label>
          <td>The subject is uninteresting, ie: dirt
        <tr><Td><input type="radio" name="param1" id="r4" value="bracketing">
          <td><label for="r4">Bracketing</label>
          <td>A very similar photo supercedes this one
      </table>
          <p><input type="submit" value="Flag Photo">
          <a href="<?= $hrefinquery . 'referer='.urlencode($_SERVER['HTTP_REFERER']) ?>"
           onclick="document.getElementById('delete').style.display='none';return false">(cancel)</a> </p>
    </div>
  </form>
<?php
  }
  
  echo "<div style=\"text-align: center\">\n";
  $alt = htmlentities($photo->Get('description'));
  echo "<img id=\"curphoto\" style=\"margin: 0 auto; width: 100%\" src=\"".$photo->GetMedia('scaled')."\" alt=\"$alt\">\n\n";
  echo "</div>\n";
?>

</body>
</html>
