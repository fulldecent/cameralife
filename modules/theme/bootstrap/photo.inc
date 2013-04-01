<?php
/**
 * Photo view 
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

# $cameralife and $photo are set by ShowPage()
# Display a photo, edit a photo, link to relevant places

$cameralife->GetFeature('security');
$cameralife->GetFeature('photostore');
$cameralife->GetFeature('imageprocessing');

if (isset($_GET['referer']))
  $_SERVER['HTTP_REFERER'] = $_GET['referer'];
if (isset($_SERVER['HTTP_REFERER'])) {
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

## Extra header...
$headers = '<link rel="stylesheet" type="text/css" href="'.$cameralife->base_url.'/modules/theme/'.$cameralife->GetPref('theme').'/delicious.css" />'.PHP_EOL;

if($photoPrev) {
  $previcon = $photoPrev->GetIcon();
  $previcon['href'] .= strpos($previcon['href'], '&#63;') ? '&amp;' : '&#63;';
  $prevhref = $previcon['href'].'referer='.urlencode($_SERVER['HTTP_REFERER']);
  $headers .= "<link rel=\"prev\" href=\"$prevhref\" />\n";
} 
if($photoNext) {
  $nexticon = $photoNext->GetIcon();
  $nexticon['href'] .= strpos($nexticon['href'], '&#63;') ? '&amp;' : '&#63;';
  $nexthref = $nexticon['href'].'referer='.urlencode($_SERVER['HTTP_REFERER']);
  $headers .= "<link rel=\"next\" href=\"$nexthref\">\n";
}

$headers .= '<script type="text/javascript" src="'.$cameralife->base_url.'/modules/theme/'.$cameralife->GetPref('theme').'/photo.js"></script>';
$headers .= '<script type="text/javascript" src="'.$cameralife->base_url.'/modules/theme/'.$cameralife->GetPref('theme').'/ajax.js"></script>';

### Set up sidebar menu
$actions = array(); // name=>, icon=>, href=>, onclick=>
$actions[] = array('name'=>"View full-sized ({$photo->Get('width')} x {$photo->Get('height')}px)",
                   'icon'=>'fullscreen',
                   'href'=>$photo->GetMedia('photo'));
$sizes = preg_split('/[, ]+/',$cameralife->GetPref('optionsizes'));
sort($sizes);
if (count($sizes)) foreach ($sizes as $size)
{
  $actions[] = array('name'=>"View at ${size}px",
                     'icon'=>'fullscreen',
                     'href'=>html_entity_decode($photo->GetMedia($size)));
}
if ($cameralife->Security->authorize('photo_rename'))
  $actions[] = array('name'=>"Rename photo",
                     'icon'=>'font',
                     'href'=>html_entity_decode(AddParamRawUrlEncode('action','rename')),
                     'onclick'=>"$('#showHideRenameForm').hide();$('#renameform').show();$('#formtitle').focus();return false");
if ($cameralife->Security->authorize('photo_delete'))
  $actions[] = array('name'=>"Flag photo",
                     'icon'=>'flag',
                     'href'=>html_entity_decode(AddParamRawUrlEncode('action','flag')),
                     'onclick'=>"$('#delete').show();return false");
if ($cameralife->Security->authorize('photo_modify'))
  $actions[] = array('name'=>"Modify photo (rotate, revert, ...)",
                     'icon'=>'wrench',
                     'href'=>html_entity_decode(AddParamRawUrlEncode('action','modify')),
                     'onclick'=>"$('#modify').show();return false");
if ($photo->Get('status') == 0)
  $actions[] = array('name'=>"Order prints online",
                     'icon'=>'print',
                     'href'=>html_entity_decode(AddParamRawUrlEncode('action','print')),
                     'onclick'=>"$('#print').show();return false");
$menu = <<<HTML
<ul class="nav nav-list">
  <li class="nav-header">Actions</li>
HTML;
foreach ($actions as $action) {
  $menu .= "  <li><a href=\"".htmlentities($action['href'])."\"";
  if (isset($action['onclick']))
    $menu .= " onclick=\"{$action['onclick']}\"";
  $menu .= "><i class=\"icon-{$action['icon']}\"></i> {$action['name']}</a></li>\n";
}
$menu .= <<<HTML
</ul>
HTML;
/*


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
*/


$cameralife->Theme->Header($photo->Get('description'), $menu, $headers);

  if ($photo->Get('status') > 0)
  {
?>
  <div class="alert">This photo is not public</div>
<?php
  }
?>
  <h1>
    <?= htmlentities($photo->Get('description'), NULL, "UTF-8") ?>
<?php
  if ($cameralife->Security->authorize('photo_rename'))
  {
?>
    <a href="<?= AddParamRawUrlEncode('action','rename') ?>" 
       id="showHideRenameForm"
       class="btn btn-mini" 
       style="<?=(isset($_GET['action']) && $_GET['action'] == 'rename')?'display:none':''?>"
       onclick="$('#showHideRenameForm').hide();$('#renameform').show();$('#formtitle').focus();return false">edit</a>
<?php 
  } 
?>
  </h1>
<?php

  if ($cameralife->Security->authorize('photo_rename'))
  {
?>
    <form action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST" name="form" id="renameform" class="well form-horizontal" style="<?=(isset($_GET['action']) && $_GET['action'] == 'rename')?'':'display:none'?>">
      <input type="hidden" name="id" value="<?= $photo->Get('id') ?>">
      <input type="hidden" name="target" value="<?= $icon['href'] ?>">
      <input type="hidden" name="action" value="rename">
      <legend>The <strong>Title</strong> shows on this page and thumbnails of this photo elsewhere. <strong>Keywords</strong> are never displayed. Searches and Albums use the <strong>Title</strong> and <strong>Keywords</strong> to find photos.</legend>
      <div class="control-group">
        <label class="control-label" for="formtitle">Title</label>
        <div class="controls">
          <input name="param1" type="text" id="formtitle" placeholder="Title" class="input-large" value="<?= htmlentities($photo->Get('description'), NULL, "UTF-8") ?>">
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="tags">Keywords</label>
        <div class="controls">
          <input name="param2" type="text" id="tags" placeholder="Keywords" class="input-xxlarge" value="<?= htmlentities($photo->Get('tags'), NULL, "UTF-8") ?>">
          <span class="help-inline">space separated</span>
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <input class="btn btn-primary" type="submit" value="Save Changes" onclick="makeRequest('<?= $cameralife->base_url ?>/photo_controller.php','renameform',renameDone);return false">
          <a class="btn" href="<?= $icon['href'] ?>" onclick="$('#showHideRenameForm').show();$('#renameform').hide();return false">Cancel</a> 
        </div>
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
    <form action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST" name="form" id="delete" class="well form-horizontal" style="<?=(isset($_GET['action']) && $_GET['action'] == 'flag')?'':'display:none'?>">
      <input type="hidden" name="id" value="<?= $photo->Get('id') ?>" />
      <input type="hidden" name="target" value="<?= AddParam($icon['href'], 'referer', urlencode($_SERVER['HTTP_REFERER'])) ?>" />
      <input type="hidden" name="action" value="flag" />
      <legend>Thank you for taking the time to report bad content. Please choose the obvious problem with this photo.</legend>
      
      <label class="radio">
        <input type="radio" name="param1" id="r1" value="indecent">
        <strong>Indecent or Sensitive</strong> / Obsenity, nudity
      </label>
      <label class="radio">
        <input type="radio" name="param1" id="r1" value="photography">
        <strong>Bad photography</strong> / Blurry, poor lighting, terrible cropping
      </label>
      <label class="radio">
        <input type="radio" name="param1" id="r1" value="subject">
        <strong>Poor subject</strong> / The subject is uninteresting, eg: dirt
      </label>
      <label class="radio">
        <input type="radio" name="param1" id="r1" value="bracketing">
        <strong>Bracketing</strong> / A very similar photo supersedes this one
      </label>
      <input class="btn btn-primary btn-danger" type="submit" value="Flag Photo" onclick="makeRequest('<?= $cameralife->base_url ?>/photo_controller.php','renameform',renameDone);return false">
      <a class="btn" href="<?= $icon['href'] ?>" onclick="$('#delete').hide();return false">Cancel</a> 
    </form>
<?php
  }
  if ($cameralife->Security->authorize('photo_modify'))
  {
?>
    <div id="modify" class="well" style="<?=(isset($_GET['action']) && $_GET['action'] == 'modify')?'':'display:none'?>">
      <legend>Modify photo. All modifications can be reverted.</legend>

      <form class="form-horizontal" action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST">
        <input type="hidden" name="id" value="<?= $photo->Get('id') ?>" />
        <input type="hidden" name="target" value="<?= $icon['href'] ?>" />
        <input type="hidden" name="action" value="rotate" />
        <div class="control-group">
          <label class="control-label" for="tags">Rotate</label>
          <div class="controls">
            <input class="btn" type="submit" name="param1" value="Rotate Counter-Clockwise">
            <input class="btn" type="submit" name="param1" value="Rotate Clockwise">
          </div>
        </div>
      </form>
      
      <form class="form-horizontal" action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST">
        <input type="hidden" name="id" value="<?= $photo->Get('id') ?>" />
        <input type="hidden" name="target" value="<?= $icon['href'] ?>" />
        <input type="hidden" name="action" value="revert" />
        <div class="control-group">
          <label class="control-label" for="tags">Revert</label>
          <div class="controls">
            <input class="btn" type="submit" name="param1" value="Revert">
          </div>
        </div>
      </form>

      <form class="form-horizontal" action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST">
        <input type="hidden" name="id" value="<?= $photo->Get('id') ?>" />
        <input type="hidden" name="target" value="<?= $icon['href'] ?>" />
        <input type="hidden" name="action" value="rethumb" />
        <div class="control-group">
          <label class="control-label" for="tags">Regenerate Thumbnail</label>
          <div class="controls">
            <input class="btn" type="submit" name="param1" value="Regenerate Thumbnail">
          </div>
        </div>
      </form>
      
<!--
      <script type="text/javascript" src="http://feather.aviary.com/js/feather.js"></script>
      <div class="form-horizontal">
        <div class="control-group">
          <label class="control-label">Edit photo</label>
          <div class="controls">
            <a class="btn" onclick="return launchEditor('image1', 'http://images.aviary.com/imagesv5/feather_default.jpg');">Edit photo with Aviary</a>
          </div>
        </div>
      </div>
-->
      
      <div class="form-horizontal">
        <div class="control-group">
          <div class="controls">
            <a class="btn" href="<?= $icon['href'] ?>" onclick="$('#modify').hide();return false">Cancel</a> 
          </div>
        </div>
      </div>
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
  if ($photo->Get('status') == 0)
  {
?>
    <form action="http://www.digibug.com/dapi/order.php" method="POST" name="form" id="print" class="well form-horizontal" style="<?=(isset($_GET['action']) && $_GET['action'] == 'print')?'':'display:none'?>">
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
      <legend>You can order prints of this photo from the Camera Life store.</legend>
      <input class="btn btn-primary" type="submit" value="Order">
      <a class="btn" href="<?= $icon['href'] ?>" onclick="$('#print').hide();return false">Cancel</a> 
    </form>
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

 // echo "<div class='thumbnail' style=\"text-align: center; margin: 0 auto\">\n";
  $alt = htmlentities($photo->Get('description'));
  echo "<img id=\"curphoto\" style=\"display: block; padding: 4px; border: 1px solid #DDD; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; margin: 0 auto\" src=\"".$photo->GetMedia('scaled')."\" alt=\"$alt\">\n\n";
//  echo "</div>\n";
?>

<div class="row-fluid">
  <div class="span6">
    <h2>Information</h2>
    <dl class="dl-horizontal">
<?php
if ($photo->Get('username')) {
  echo '         <dt>Author</dt><dd>'.$photo->Get('username').'</dd>';
}

if ($exif = $photo->GetEXIF()) {
  foreach ($exif as $key=>$val) {
    if ($key=="Location")
      echo "         <dt>$key</dt><dd><a href=\"http://maps.google.com/maps?q=$val\">$val</a></dd>\n";
    else if ($key=="Camera Model")
      echo "         <dt>$key</dt><dd><a href=\"http://pbase.com/cameras/$val\">$val</a></dd>\n";
    else
      echo "         <dt>$key</dt><dd>$val</dd>\n";
  }
}
?>    
    </dl>
  </div>
  <div class="span6">
    <h2>Feedback</h2>
    <form action="<?= $cameralife->base_url?>/photo_controller.php" method=POST name="form">
      <input type="hidden" name="id" value="<?= $photo->Get('id') ?>">
      <input type="hidden" name="target" value="<?= $icon['href'] ?>">
      <input type="hidden" name="action" value="rate">
    
      <dl class="dl-horizontal">
        <dt>My rating</dt>
        <dd>
          <div class="btn-group">

<?php
for ($i=1; $i<=5; $i++) {
  echo "<button name=\"param1\" value=\"$i\" type=\"submit\" class=\"btn btn-mini\">";
  if ($i<=$rating)
    echo "<i class=\"icon-star\"></i>\n";
  else
    echo "<i class=\"icon-star-empty\"></i>\n";
  echo "</button>\n";
}
?>
          </div>
        </dd>
        <dt>Average rating</dt>
        <dd>
<?php
for ($i=1; $i<=5; $i++) {
  if ($i<=$avg)
    echo "<i class=\"icon-star\"></i>\n";
  else
    echo "<i class=\"icon-star-empty\"></i>\n";
}
?>
        </dd>
      </dl>
    </form>
    <h2>Comments</h2>
      <?php 

$result = $cameralife->Database->Select('comments', '*', 'photo_id='.$photo->Get('id'));
while ($comment = $result->FetchAssoc())
echo "<strong>".$comment['username']."</strong> <em>" . date('Y-m-d', strtotime($comment['date']))."</em><br>" .htmlentities($comment['comment']) . "<hr>";
  echo "</p>";
      ?>
      <form action="<?= $cameralife->base_url ?>/photo_controller.php" method=POST name="form">
<input type="hidden" name="id" value="<?= $photo->Get('id') ?>">
<input type="hidden" name="target" value="<?= $icon['href'] ?>">
<input type="hidden" name="action" value="comment">
<input name="param1">
<input type="submit" value="Post comment" class="btn">
</form>
  </div>
</div>

<?php
  # Cache the next image the user is likely to look at
  if ($photoNext) {
      echo '<img style="display:none" src="'.$photoNext->GetMedia('scaled').'" alt="hidden photo">';
  }
  
  $cameralife->Theme->Footer();
?>
