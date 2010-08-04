<?php
/**Enables 
*<ul>
*<li>displaying/viewing of photos</li>
*<li>editing of photos</li>
*<li>linking to relevant locations for further operations </li>
*<li>caching of most likely viewed pictures</li>
* if ($photoNext)
 * {
 *   echo '<img style="display:none" src="'.$photoNext->GetMedia('scaled').'" alt="hidden photo">';
 * }
*</ul>
*<b>Note</b>$cameralife and $photo are set by ShowPage()
* <code>
*if (!$cameralife->Security->GetName())
   * {
   *   $condition = "user_ip='".$cameralife->Security->GetAddr()."' AND user_name=''";
  *    $count_edits = $cameralife->Database->SelectOne('logs','COUNT(*)',$condition);
  *    if ($count_edits % 3 == 0)
    *    $ask_user_to_login = true;
    *}
*</code>The above code prompts if user is doing useful edits prompts them to log in or register 
 
 * <code>
 *if ($photoNext)
 * {
 *   echo '<img style="display:none" src="'.$photoNext->GetMedia('scaled').'" alt="hidden photo">';
 * }
 *</code>
 *Caches the next image the user is likely to look at
*@link http://fdcl.sourceforge.net
  *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */
  /**
  */
  # $cameralife and $photo are set by ShowPage()
  # Display a photo, edit a photo, link to relevant places

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');
  $cameralife->GetFeature('imageprocessing');

  if ($photo->Get('status') != 0) 
    $cameralife->Security->authorize('admin_file', 'This file has been flagged or marked private');

  if (isset($_GET['referer']))
    $_SERVER['HTTP_REFERER'] = $_GET['referer'];
  if (isset($_SERVER['HTTP_REFERER']))
  {
    $_SERVER['HTTP_REFERER'] = str_replace($cameralife->base_url,'', $_SERVER['HTTP_REFERER']);
    $_SERVER['HTTP_REFERER'] = preg_replace('|^/|', '', $_SERVER['HTTP_REFERER']);
  }

  # Handy stuff
  $icon = $photo->GetIcon('small');
  if (isset($_SERVER['HTTP_REFERER']))
    $icon['href'] .= (strpos($icon['href'], '&#63;') ? '&amp;' : '&#63;') . 'referer='.urlencode($_SERVER['HTTP_REFERER']);
  $photoPrev = $photo->GetPrevious();
  $photoNext = $photo->GetNext();

  if ($cameralife->Security->GetName())
    $rating = $avg = $cameralife->Database->SelectOne('ratings', 'AVG(rating)', 'id='.$photo->Get('id')." AND username='".$cameralife->Security->GetName()."'");
  else
    $rating = $avg = $cameralife->Database->SelectOne('ratings', 'AVG(rating)', 'id='.$photo->Get('id')." AND user_ip='".$_SERVER['REMOTE_ADDR']."'");

/*    // If the user is not logged in and is making helpful edits, invite them to register / login
    if (!$cameralife->Security->GetName())
    {
      $condition = "user_ip='".$cameralife->Security->GetAddr()."' AND user_name=''";
      $count_edits = $cameralife->Database->SelectOne('logs','COUNT(*)',$condition);
      if ($count_edits % 3 == 0)
        $ask_user_to_login = true;
    }
*/
  ## Extra header...
  $headers = '<link rel="stylesheet" type="text/css" href="delicious.css">';
  
  if($photoPrev)
  {
    $previcon = $photoPrev->GetIcon();
    $previcon['href'] .= strpos($previcon['href'], '&#63;') ? '&amp;' : '&#63;';
    $prevhref = $previcon['href'].'referer='.urlencode($_SERVER['HTTP_REFERER']);
    $headers .= "<link rel=\"prev\" href=\"$prevhref\">\n";
  }
  if($photoNext)
  {
    $nexticon = $photoNext->GetIcon();
    $nexticon['href'] .= strpos($nexticon['href'], '&#63;') ? '&amp;' : '&#63;';
    $nexthref = $nexticon['href'].'referer='.urlencode($_SERVER['HTTP_REFERER']);
    $headers .= "<link rel=\"next prefetch\" href=\"$nexthref\">\n";
  }

  $headers .= '<script type="text/javascript" src="'.$cameralife->base_url.'/modules/theme/'.$cameralife->GetPref('theme').'/keywords.php"></script>';
  $headers .= '<script type="text/javascript" src="'.$cameralife->base_url.'/modules/theme/'.$cameralife->GetPref('theme').'/delicious.js"></script>';
  $headers .= '<script type="text/javascript" src="'.$cameralife->base_url.'/modules/theme/'.$cameralife->GetPref('theme').'/photo.js"></script>';
  $headers .= '<script type="text/javascript" src="'.$cameralife->base_url.'/modules/theme/'.$cameralife->GetPref('theme').'/ajax.js"></script>';
  $headers .= '<script type="text/javascript">';

  $suggestions = $photo->Get('path');
  foreach (explode('/', $suggestions) as $keyword)
    $keys['"'.preg_replace('/[^a-z0-9]/i','',$keyword).'"']=1;
  $headers .= 'var copytags=['.implode(',',array_keys($keys))."]\n";
  $headers .= 'var rating='.($rating+0)."\n";
  $headers .= 'var cameralifebaseurl="'.$cameralife->base_url.'"';
  $headers .='</script>';

  $cameralife->Theme->Header(htmlentities($photo->Get('description')), 'photo', $headers, 'init()');

  echo "     <div id=\"sidebar\">\n";
  echo "       <h1>Actions</h1>\n";
  echo "       <ul>\n";

  echo "       <li><a rel=\"nofollow\" href=\"".$photo->GetMedia('photo')."\">
               View full-sized (".$photo->Get('width')." x ".$photo->Get('height').")</a></li>\n";

  $sizes = preg_split('/[, ]+/',$cameralife->GetPref('optionsizes'));
  sort($sizes);
  if (count($sizes)) foreach ($sizes as $size)
  {
    echo " <li>";
    echo " <a rel=\"nofollow\" href=\"".$photo->GetMedia($size)."\">";
    $cameralife->Theme->Image('small-photo',array('align'=>'middle'));
    echo " View at ${size}px</a>\n";
    echo "</li>";
  }

  // Show all the tasks the user can do (if they're allowed)
  if ($cameralife->Security->authorize('photo_rename'))
  {
    $href = $icon['href'].'&amp;action=rename';
    echo "       <li><a href=\"$href\" onClick=\"document.getElementById('rename').style.display='';focusTo(\$id('formtitle'),0);return false;\">Rename photo</a></li>\n";
  }
  if ($cameralife->Security->authorize('photo_delete'))
  {
    $href = $icon['href'].'&amp;action=delete';
    echo "       <li><a href=\"$href\" onClick=\"document.getElementById('delete').style.display='';return false\">Flag / Report photo'</a></li>\n";
  }
  if ($cameralife->Security->authorize('photo_modify'))
  {
    $href = $icon['href'].'&amp;action=modify';
    echo "       <li><a href=\"$href\" onClick=\"document.getElementById('modify').style.display='';return false\">Modify (rotate, revert, ...)</a></li>\n";
  }
  if ($cameralife->Security->authorize('admin_albums') && 
      isset($_SERVER['HTTP_REFERER']) && 
      (preg_match ("#album.php\?id=([0-9]+)#",$_SERVER['HTTP_REFERER'],$regs) || preg_match("#albums/([0-9]+)#",$_SERVER['HTTP_REFERER'],$regs)))
  {
#TODO a separate controller?
    $album = new Album($regs[1]);
    $alicon = $album->GetIcon('small');
    if (strpos($alicon['href'], '&#63;') !== FALSE)
      $alicon['href'] .= '&poster_id='.$photo->Get('id');
    else 
      $alicon['href'] .= '?poster_id='.$photo->Get('id');
    $name = 'Use this for <b>'.$album->Get('name').'</b>';
    $href = $alicon['href'];
    echo "       <li><a href=\"$href\">$name</a></li>\n";
  }
  if ($photo->Get('status') == 0)
  {
    $href = $icon['href'].'&amp;action=print';
    echo "       <li><a href=\"$href\">Order prints</a></li>\n";
  }

  echo "     </ul>\n";
  echo "     <h1>Related photos</h1>\n";
  echo "     <ul>\n";

  $context = $photo->GetRelated();
  foreach ($context as $cicon)
  {
    echo "       <li><a href=\"{$cicon['href']}\">{$cicon['name']}</a></li>\n";
  }

  echo "      </ul>";

  if($photoPrev)
  {
    $img = $photoPrev->GetMedia('thumbnail');
    echo "       <div class=\"item\"><a href='$prevhref'><img id=\"prevphoto\" style=\"margin: 0 10px\" src=\"$img\" alt=\"{$previcon['name']}\"></a>\n";
    echo "       <a style=\"clear: left;\" href='$prevhref'>Previous photo</a><span class=\"description\">{$previcon['name']}</span></div>\n";
  }
  if($photoNext)
  {
    $img = $photoNext->GetMedia('thumbnail');
    echo "       <div class=\"item\"><a href='$nexthref'><img id=\"nextphoto\" style=\"margin: 0 10px\" src=\"$img\" alt=\"{$nexticon['name']}\"></a>\n";
    echo "       <a style=\"clear: left;\" href='$nexthref'>Next photo</a><span class=\"description\">{$nexticon['name']}</span></div>\n";
  }

  echo "    </div>"; # Sidebar

  if ($photo->Get('status') > 0)
  {
?>
    <div class="administrative" align="center">
      <p>This photo is not public</p>
    </div>
<?php
  }
  if ($cameralife->Security->authorize('photo_rename'))
  {
?>
    <form action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST" name="form" id="renameform">
    <input type="hidden" name="id" value="<?= $photo->Get('id') ?>">
    <input type="hidden" name="target" value="<?= $icon['href'] ?>">
    <input type="hidden" name="action" value="rename">

    <div style="<?=(isset($_GET['action']) && $_GET['action'] == 'rename')?'':'display:none'?>" class="administrative" align=center id="rename">
      <table>
        <tr><td colspan=3><p>This <b>Title</b> will be shown on this page and on thumbnails of this photo elsewhere. <b>Keywords</b> will never be displayed. Searches and Albums both use the <b>Title</b> and <b>Keywords</b> to find photos.</p>
        <tr><td>Title:<td colspan=2><input id="formtitle" style="width: 100%;" type="text" name="param1" value="<?= htmlentities($photo->Get('description')) ?>">
      <?php
        $origname = ucwords($photo->Get('filename'));
        $origname = preg_replace('/.[^.]+$/', '', $origname);

        if (!preg_match('/^dscn/i', $origname) && !preg_match('/^im/i', $origname)) // useless filename
          echo '<tr><td><td><input type="button" onclick="javascript:set(\''.addslashes($origname).'\')" value="Set name to '.$origname.'">';
      ?>
        <tr><td>Keywords:
          <td style="width:100%"><input style="width: 100%;" id="tags" name="param2" value="<?= htmlentities($photo->Get('keywords')) ?>" autocomplete="off" type="text">
          <td>space&nbsp;separated
        <tr style="visibility: hidden; background:pink" id="suggestions">
          <td class="rs">completions
          <td><div id="suggest"></div>
        <tr>
          <td><i>suggested</i>
          <td colspan=2 style="border-bottom: 1px solid #2F4C5D;">
            <span id="copy">
            </span>
        <tr valign=top>
          <td>
              <i><a id="alphasort" class="noclicky" href="javascript:sort('alpha')">all</a
              >&nbsp;|&nbsp;<a id="freqsort" class="clicky" href="javascript:sort('freq')">common</a></i>
          <td colspan=2>
            <div id="alpha">
            </div>
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
    {
      $target = $nexthref;
    }
    elseif ($photoPrev)
    {
      $target = $prevhref;
    }
    elseif (isset($_SERVER['HTTP_REFERER']))
      $target = $cameralife->base_url.'/'.$_SERVER['HTTP_REFERER'];
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
  if ($cameralife->Security->authorize('photo_modify'))
  {
?>
    <div style="<?=($_GET['action'] == 'modify')?'':'display:none'?>"class="administrative" align=center id="modify">
      <form action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST">
      Rotate: <input type="hidden" name="id" value="<?= $photo->Get('id') ?>" />
      <input type="hidden" name="target" value="<?= $icon['href'] ?>" />
      <input type="hidden" name="action" value="rotate" />
        <input type="submit" name="param1" value="Rotate Counter-Clockwise">
        <input type="submit" name="param1" value="Rotate Clockwise">
      </form>

      <form action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST">
      Revert: <input type="hidden" name="id" value="<?= $photo->Get('id') ?>" />
      <input type="hidden" name="target" value="<?= $icon['href'] ?>" />
      <input type="hidden" name="action" value="revert" />
        <input type="submit" name="param1" value="Revert">
      </form>

      <form action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST">
      Regenerate Thumbnail: <input type="hidden" name="id" value="<?= $photo->Get('id') ?>" />
      <input type="hidden" name="target" value="<?= $icon['href'] ?>" />
      <input type="hidden" name="action" value="rethumb" />
        <input type="submit" name="param1" value="Regenerate Thumbnail">
      </form>

      <a href="<?= $icon['href'] ?>"
         onclick="document.getElementById('modify').style.display='none';return false">(cancel)</a> 
    </div>
<?php
  }
  elseif ($ask_user_to_login == true)
  {
?>
    <div class="administrative" align=center>
      Thank you for your contributions to <?= $cameralife->GetPref('sitename') ?>, please consider
      <?php $cameralife->Theme->Image('small-login',array('align'=>'middle')) ?>
      <b><a href="<?= $cameralife->base_url ?>/login.php">registering or logging in</a></b>.
    </div>
<?php
  }
  if ($photo->Get('status') == 0 && $_GET['action'] == 'print')
  {
?>
    <div class="administrative" align=center>
    <form action="http://www.digibug.com/dapi/order.php" method="post">
    <p>You can order prints of this photo from the Camera Life store.</p>
      <input type="hidden" name="digibug_api_version" value="100">
      <input type="hidden" name="company_id" value="1941">
      <input type="hidden" name="event_id" value="5755">
      <input type="hidden" name="cmd" value="addimg">
      <input type="hidden" name="return_url" value="<?= $icon['href'] ?>">
      <input type="hidden" name="num_images" value="2">
      <input type="hidden" name="image_1" value="<?= $photo->GetMedia('photo') ?>">
      <input type="hidden" name="image_height_1" value="<?= $photo->Get('height') ?>">
      <input type="hidden" name="image_width_1" value="<?= $photo->Get('width') ?>">
      <input type="hidden" name="thumb_1" value="<?= $photo->GetMedia('scaled') ?>">
      <input type="hidden" name="thumb_height_1" value="<?= $photo->Get('tn_height') ?>">
      <input type="hidden" name="thumb_width_1" value="<?= $photo->Get('tn_width') ?>">
      <input type="hidden" name="title_1" value="<?= $photo->Get('description') ?>">
      <input type="submit" value="Order">
    </form>
    </div>
<?php
  }

  if (isset($cameralife->receipt))
  {
    echo '<div id="receipt" class="receipt">';
    echo '<form action="'.$cameralife->base_url.'/undo_controller.php" method="post">';
    echo '<input id="receiptid" type="hidden" name="id" value="'.$cameralife->receipt->Get('id').'" />';
    echo '<input type="hidden" name="action" value="undo" />';
    echo '<input type="hidden" name="target" value="'.$icon['href'].'" />';
    echo $cameralife->receipt->GetDescription().' <button type="submit">Undo</button></form></div>';
  }
  else
  { 
    echo '<div id="receipt" class="receipt" style="visibility: hidden"></div>';
  }

  echo "<div style=\"text-align: center\">\n";
  $alt = htmlentities($photo->Get('description'));
  echo "<img id=\"curphoto\" style=\"margin: 0 auto\" src=\"".$photo->GetMedia('scaled')."\" alt=\"$alt\">\n\n";
  echo "</div>\n";
?>

<table width="100%">
  <tr valign=top>
    <td width="49%">
      <h2>Information</h2>
        <ul>
      <?php
        if ($photo->Get('username'))
        {
          echo '         <li>This photo was added by '.$photo->Get('username').'</li>';
        }

        if ($exif = $photo->GetEXIF())
        {
          foreach ($exif as $key=>$val)
          {
            if ($key=="Location")
              echo "         <li>$key: <a href=\"http://maps.google.com/maps?q=$val\">$val</a></li>";
            else
              echo "         <li>$key: $val</li>";
          }
        }

      ?>
      </ul>
    <td width="2%">
    <td width="49%">
    <form action="<?= $cameralife->base_url?>/photo_controller.php" method=POST name="form">
    <input type="hidden" name="id" value="<?= $photo->Get('id') ?>">
    <input type="hidden" name="target" value="<?= $icon['href'] ?>">
    <input type="hidden" name="action" value="rate">
      <h2>Feedback</h2>
      <?php 
  echo "My rating:\n";
  $unlit = $cameralife->IconURL('unlit');
  $lit = $cameralife->IconURL('lit');

  echo "<span id=\"rating\">";
  for ($i=1;$i<=5;$i++)
  {
    $hrefjs = ($i!=1) ? '' : "onclick=\"document.getElementById('delete').style.display='';return false\"";
    $style = 'style="border:0; background:none; padding:0; margin:0"';
    $imgsrc = ($i <= $rating) ? $lit : $unlit;
    $img = "<img id=\"rate$i\" src=\"$imgsrc\" alt=\"star\">";
 
    echo "<button name=\"param1\" value=\"$i\" $hrefjs type=\"submit\" onmouseover=\"overstar($i)\" onmouseout=\"nostar()\" $style>$img</button>";
  }
  if ($rating == 5)
    echo "(saved in my favorite photos)";

  echo "</span><br>";
  $avg = $cameralife->Database->SelectOne('ratings', 'AVG(rating)', 'id='.$photo->Get('id'));
  echo "Average rating: $avg\n";
  echo "<p>";
$result = $cameralife->Database->Select('comments', '*', 'photo_id='.$photo->Get('id'));
while ($comment = $result->FetchAssoc())
echo "<strong>".$comment['username']."</strong> <em>" . date('Y-m-d', strtotime($comment['date']))."</em><br>" .htmlentities($comment['comment']) . "<hr>";
  echo "</p>";
      ?>
</form>

<form action="<?= $cameralife->base_url ?>/photo_controller.php" method=POST name="form">
<input type="hidden" name="id" value="<?= $photo->Get('id') ?>">
<input type="hidden" name="target" value="<?= $icon['href'] ?>">
<input type="hidden" name="action" value="comment">
<input name="param1">
<input type="submit" value="Post comment">
</form>
    </table>

<?php
  # Cache the next image the user is likely to look at
  if ($photoNext)
  {
    echo '<img style="display:none" src="'.$photoNext->GetMedia('scaled').'" alt="hidden photo">';
  }
?>

</body>
</html>
