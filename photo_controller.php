<?php
/**
 * Handles the POST form action from photo.php
 * Pass the following variables
 * <ul>
 * <li>id = the photo id</li>
 * <li>action = the action to perform on the photo</li>
 * <li>param1 = extra info</li>
 * <li> param2 = extra info</li>
 * <li>target = the exit URL, or 'ajax' for an ajax call</li></ul>
 * @author Will Entriken <cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2001-2009 Will Entriken
*/

$features=array('imageprocessing','security','filestore');
require 'main.inc';

is_numeric($_POST['id'])
  and $photo = new Photo($_POST['id'])
  or $cameralife->error('this photo does not exist');
if ($photo->get('status') != 0)
  $cameralife->Security->authorize('admin_file', 'This file has been flagged or marked private');

if ($_POST['action'] == 'flag') {
  $cameralife->Security->authorize('photo_delete',1);

  if (!$_POST['param1']) $cameralife->error('Parameter missing.');

  $photo->set('flag', $_POST['param1']);
  $receipt = $photo->set('status', 1);
} elseif ($_POST['action'] == 'rename') {
  $cameralife->Security->authorize('photo_rename',1);

  $receipt = $photo->set('description', stripslashes($_POST['param1']));
  $photo->set('keywords', stripslashes($_POST['param2']));
} elseif ($_POST['action'] == 'rethumb') {
  $cameralife->Security->authorize('photo_modify',1);

  $photo->generateThumbnail();
} elseif ($_POST['action'] == 'rotate') {
  $cameralife->Security->authorize('photo_modify',1);

  if ($_POST['param1'] == 'rotate Clockwise')
    $photo->rotate(90);
  elseif ($_POST['param1'] == 'rotate Counter-Clockwise')
    $photo->rotate(270);
} elseif ($_POST['action'] == 'revert') {
  $cameralife->Security->authorize('photo_modify',1);

  $photo->revert();
} elseif ($_POST['action'] == 'comment') {
  $cameralife->Database->Insert('comments', array('photo_id'=>$photo->get('id'), 'username'=>$cameralife->Security->GetName(), 'user_ip'=>$_SERVER['REMOTE_ADDR'], 'comment'=>stripslashes($_POST['param1']), 'date'=>date('Y-m-d')));
} elseif ($_POST['action'] == 'rate') {
  if ($cameralife->Security->GetName())
      $rating = $cameralife->Database->SelectOne('ratings', 'AVG(rating)', 'id='.$_POST['id']." AND username='".$cameralife->Security->GetName()."'");
  else
      $rating = $cameralife->Database->SelectOne('ratings', 'AVG(rating)', 'id='.$_POST['id']." AND user_ip='".$_SERVER['REMOTE_ADDR']."'");

  if ($rating) {
    if ($cameralife->Security->GetName())
      $cameralife->Database->Update('ratings', array('rating'=>$_POST['param1'], 'date'=>date('Y-m-d')), 'id='.$_POST['id']." AND username='".$cameralife->Security->GetName()."'");
    else
        $cameralife->Database->Update('ratings', array('rating'=>$_POST['param1'], 'date'=>date('Y-m-d')), 'id='.$_POST['id']." AND user_ip='".$_SERVER['REMOTE_ADDR']."'");
  } else
    $cameralife->Database->Insert('ratings', array('id'=>$_POST['id'], 'username'=>$cameralife->Security->GetName(), 'user_ip'=>$_SERVER['REMOTE_ADDR'], 'rating'=>$_POST['param1'], 'date'=>date('Y-m-d')));
  $rating = $regs[1];
} else {
  $cameralife->error("Invalid action parameter", __FILE__, __LINE__);
}

if ($receipt && $_POST['target'] != 'ajax') {
  $_POST['target'] = $_POST['target'] . ((strpos($_POST['target'], '?')===FALSE)?'?':'&') . 'receipt='.$receipt->get('id');
}

if ($_POST['target'] == 'ajax') {
  if ($receipt && $receipt->isValid()) {
    header('Content-type: text/xml');
    echo '<?xml version="1.0" ?><receipt><id>'.$receipt->get('id')."</id></receipt>\n";
  }
  exit(0);
} else
  header("Location: ".$_POST['target']);
