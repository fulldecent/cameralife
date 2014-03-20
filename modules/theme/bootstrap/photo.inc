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
$cameralife->GetFeature('filestore');
$cameralife->GetFeature('imageprocessing');
$icon = $photo->GetIcon();

if (isset($_GET['referer']))
  $_SERVER['HTTP_REFERER'] = $_GET['referer'];
if (isset($_SERVER['HTTP_REFERER'])) {
  $_SERVER['HTTP_REFERER'] = str_replace($cameralife->base_url,'', $_SERVER['HTTP_REFERER']);
  $_SERVER['HTTP_REFERER'] = preg_replace('|^/|', '', $_SERVER['HTTP_REFERER']);
} else
  $_SERVER['HTTP_REFERER'] = '';

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

$cameralife->Theme->Header($photo->Get('description'), NULL, $headers);

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
       class="btn btn-sm btn-default" 
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
      <p class="lead">The <strong>Title</strong> shows on this page and thumbnails of this photo elsewhere. <strong>Keywords</strong> are never displayed. Searches and Albums use the <strong>Title</strong> and <strong>Keywords</strong> to find photos.</p>
      <input type="hidden" name="id" value="<?= $photo->Get('id') ?>">
      <input type="hidden" name="target" value="<?= $icon['href'] ?>">
      <input type="hidden" name="action" value="rename">
      <div class="form-group">
        <label for="formtitle" class="col-sm-2 control-label">Title</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="formtitle" placeholder="Title" name="param1" value="<?= htmlentities($photo->Get('description'), NULL, "UTF-8") ?>">
        </div>
      </div>
      <div class="form-group">
        <label for="tags" class="col-sm-2 control-label">Keywords</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="tags" placeholder="Keywords" name="param2" value="<?= htmlentities($photo->Get('tags'), NULL, "UTF-8") ?>">
        </div>
      </div>

      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
        
          <input class="btn btn-primary" type="submit" value="Save Changes" onclick="makeRequest('<?= $cameralife->base_url ?>/photo_controller.php','renameform',function(){$('h1').html($('form#renameform input[name=param1]').val())};return false">
          <a class="btn btn-default" href="<?= $icon['href'] ?>" onclick="$('#showHideRenameForm').show();$('#renameform').hide();return false">Cancel</a> 

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
    <form action="<?= $cameralife->base_url ?>/photo_controller.php" method="POST" name="form" id="deleteform" class="well form-horizontal" style="<?=(isset($_GET['action']) && $_GET['action'] == 'flag')?'':'display:none'?>">
      <input type="hidden" name="id" value="<?= $photo->Get('id') ?>" />
      <input type="hidden" name="target" value="<?= AddParam($icon['href'], 'referer', urlencode($_SERVER['HTTP_REFERER'])) ?>" />
      <input type="hidden" name="action" value="flag" />
      <p class="lead">Thank you for taking the time to report bad content. Please choose the obvious problem with this photo.</p>
      
      <label class="radio">
        <input type="radio" name="param1" value="indecent">
        <strong>Indecent or Sensitive</strong> / Obsenity, nudity
      </label>
      <label class="radio">
        <input type="radio" name="param1" value="photography">
        <strong>Bad photography</strong> / Blurry, poor lighting, terrible cropping
      </label>
      <label class="radio">
        <input type="radio" name="param1" value="subject">
        <strong>Poor subject</strong> / The subject is uninteresting, eg: dirt
      </label>
      <label class="radio">
        <input type="radio" name="param1" value="bracketing">
        <strong>Bracketing</strong> / A very similar photo supersedes this one
      </label>
      <input class="btn btn-primary btn-danger" type="submit" value="Flag Photo">
      <a class="btn btn-default" href="<?= $icon['href'] ?>" onclick="$('#delete').hide();return false">Cancel</a> 
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
  if ($photo->Get('status') == 0)
  {
    $json = '{"request":{"merchant":{"merchant-api-key":"","merchant-button-key":"13658928432161657"},"order":{"product":{"thumbnail":{"src":"'.$photo->GetMedia('thumbnail').'","width":"0","height":"0"},"source":{"file":{"dimensions":{"width":"'.$photo->Get('width').'","height":"'.$photo->Get('height').'","bleed":"0.3"},"usage":"full-product","filetype":"image","src":"'.$photo->GetMedia('photo').'","publicationId":"","pages":"1"}},"target":{"offering":{"id":"0"}},"title":""},"reference":""},"redirect":{"cancellation":{"href":""},"error":{"href":""},"thankyou":{"href":"'.$cameralife->base_url.'"}},"quantity":"1","currency":"USD","locale":"en_EN","filterCategory":"","filterSize":"","filterColour":"","filterOfferingId":"0"}}';
?>
    <div id="print" class="well" style="<?=(isset($_GET['action']) && $_GET['action'] == 'print')?'':'display:none'?>">
      <p class="lead">Order postcards, canvas prints and more styles of this photo.</p>
      
      <form action="http://secure.print.peecho.com/button/checkout/pay" method="post">
        <input type="hidden" name="orderRequest" value="<?= htmlentities($json) ?>">
        <input type="hidden" name="format" value="json">
        <input type="hidden" name="apiType" value="spb">
        <button class="btn btn-primary">Order prints</button>
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

 // echo "<div class='thumbnail' style=\"text-align: center; margin: 0 auto\">\n";
  $alt = htmlentities($photo->Get('description'));
  echo "<img id=\"curphoto\" style=\"display: block; padding: 4px; border: 1px solid #DDD; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; margin: 0 auto\" src=\"".$photo->GetMedia('scaled')."\" alt=\"$alt\">\n\n";
//  echo "</div>\n";
?>

<div class="row">
  <div class="col-md-4">
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
  <div class="col-md-4">
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
  echo "<button name=\"param1\" value=\"$i\" type=\"submit\" class=\"btn btn-sm btn-default\">";
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
      ?>
    <form action="<?= $cameralife->base_url ?>/photo_controller.php" method=POST name="form">
      <input type="hidden" name="id" value="<?= $photo->Get('id') ?>">
      <input type="hidden" name="target" value="<?= $icon['href'] ?>">
      <input type="hidden" name="action" value="comment">
      <input name="param1">
      <input type="submit" value="Post comment" class="btn">
    </form>
  </div>
  <div class="col-md-4">
  <h2>Actions</h2>
      
<?php

echo "<ul class=\"list-unstyled\">";
echo "<li><a href=\"".$photo->GetMediaURL('photo')."\"><i class=\"icon-fullscreen\"></i> View full-sized ({$photo->Get('width')} x {$photo->Get('height')}px)</a></li>";

if ($cameralife->Security->authorize('photo_rename'))
  echo "<li><a onClick=\"$('#showHideRenameForm').hide();$('#renameform').show();$('#formtitle').focus();return false\" href=\"".html_entity_decode(AddParamRawUrlEncode('action','rename'))."\"><i class=\"icon-font\"></i> Rename photo</a></li>";

if ($cameralife->Security->authorize('photo_delete'))
  echo "<li><a onClick=\"$('#deleteform').show();return false\" href=\"".html_entity_decode(AddParamRawUrlEncode('action','flag'))."\"><i class=\"icon-flag\"></i> Flag photo</a></li>";

if ($cameralife->Security->authorize('photo_modify'))
  echo "<li><a onClick=\"$('#modify').show();return false\" href=\"".html_entity_decode(AddParamRawUrlEncode('action','modify'))."\"><i class=\"icon-wrench\"></i> Modify photo (rotate, revert, ...)</a></li>";

if ($photo->Get('status') == 0)
  echo "<li><a onClick=\"$('#print').show();return false\" href=\"".html_entity_decode(AddParamRawUrlEncode('action','print'))."\"><i class=\"icon-print\"></i> Order prints</a></li>";

echo "<li><a href=\"http://www.facebook.com/sharer.php?u='".htmlentities($icon['href'])."\"><i class=\"icon-star\"></i> Share on Facebook</a></li>";

echo "</ul>";

/*
### Set up sidebar menu
$actions = array(); // name=>, icon=>, href=>, onclick=>
$actions[] = array('name'=>"View full-sized ({$photo->Get('width')} x {$photo->Get('height')}px)",
                   'icon'=>'fullscreen',
                   'href'=>$photo->GetMediaURL('photo'));
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
                     'onclick'=>"$('#deleteform').show();return false");
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
$menu = '<ul class="nav nav-list"><li class="nav-header">Actions</li>';
foreach ($actions as $action) {
  $menu .= "  <li><a href=\"".htmlentities($action['href'])."\"";
  if (isset($action['onclick']))
    $menu .= " onclick=\"{$action['onclick']}\"";
  $menu .= "><i class=\"icon-{$action['icon']}\"></i> {$action['name']}</a></li>\n";
}
$menu .= '</ul>';
$menu .= '<ul class="nav nav-list"><li class="nav-header">Share</li>';
$menu .= "  <li><a href=\"http://www.facebook.com/sharer.php?u='".htmlentities($icon['href'])."\"><i class=\"icon-star\"></i> Share on Facebook</a></li>\n";
$menu .= '</ul>';

  echo $menu;
  */
?>
    </div>
  </div>

<?php
  # Cache the next image the user is likely to look at
  if ($photoNext) {
      echo '<img style="display:none" src="'.$photoNext->GetMedia('scaled').'" alt="hidden photo">';
  }
  
  $cameralife->Theme->Footer();
?>
