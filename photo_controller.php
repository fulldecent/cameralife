<?php
  # Handle all POST form actions from photo.php
  #
  # Pass me variables:
  # id = the photo id
  # action = the action to perform on the photo
  # param1 = extra info
  # param2 = extra info...
  # target = the exit URL, or 'ajax' for an ajax call

  $features=array('database', 'imageprocessing', 'security', 'photostore');
  require "main.inc";

  $photo = new Photo($_POST['id'])
    or $cameralife->Error('this photo does not exist');
  if ($photo->Get['status'] != 0) 
    $cameralife->Security->authorize('admin_file', 'This file has been flagged or marked private');

  if ($_POST['action'] == 'flag')
  {
    $cameralife->Security->authorize('photo_delete',1);

    if (!$_POST['param1']) $cameralife->Error('Parameter missing.');

    $photo->Set('flag', substr($_POST['param1'], 1,1));
    $receipt = $photo->Set('status', 1);
  }
  elseif ($_POST['action'] == 'rename')
  {
    $cameralife->Security->authorize('photo_rename',1);

    $receipt = $photo->Set('description', stripslashes($_POST['param1']));
    $photo->Set('keywords', stripslashes($_POST['param2']));
  }
  elseif ($_POST['action'] == 'rethumb')
  {
    $cameralife->Security->authorize('photo_modify',1);

    $photo->GenerateThumbnail();
  }
  elseif ($_POST['action'] == 'rotate')
  {
    $cameralife->Security->authorize('photo_modify',1);

    if ($_POST['param1'] == 'Rotate Clockwise')
      $photo->Rotate(90);
    elseif ($_POST['param1'] == 'Rotate Counter-Clockwise')
      $photo->Rotate(270);
  }
  elseif ($_POST['action'] == 'revert')
  {
    $cameralife->Security->authorize('photo_modify',1);

    $photo->Revert();
  }
  elseif ($_POST['action'] == 'comment')
  {
    $cameralife->Database->Insert('comments', array('photo_id'=>$photo->Get('id'), 'username'=>$cameralife->Security->GetName(), 'user_ip'=>$_SERVER['REMOTE_ADDR'], 'comment'=>stripslashes($_POST['param1']), 'date'=>date('Y-m-d')));
  }
  elseif ($_POST['action'] == 'rate')
  {
    if ($cameralife->Security->GetName())
        $rating = $cameralife->Database->SelectOne('ratings', 'AVG(rating)', 'id='.$_POST['id']." AND username='".$cameralife->Security->GetName()."'");
    else
        $rating = $cameralife->Database->SelectOne('ratings', 'AVG(rating)', 'id='.$_POST['id']." AND user_ip='".$_SERVER['REMOTE_ADDR']."'");

    if ($rating)
    {
      if ($cameralife->Security->GetName())
        $cameralife->Database->Update('ratings', array('rating'=>$_POST['param1'], 'date'=>date('Y-m-d')), 'id='.$_POST['id']." AND username='".$cameralife->Security->GetName()."'");
      else
        $cameralife->Database->Update('ratings', array('rating'=>$_POST['param1'], 'date'=>date('Y-m-d')), 'id='.$_POST['id']." AND user_ip='".$_SERVER['REMOTE_ADDR']."'");
    }
    else
      $cameralife->Database->Insert('ratings', array('id'=>$_POST['id'], 'username'=>$cameralife->Security->GetName(), 'user_ip'=>$_SERVER['REMOTE_ADDR'], 'rating'=>$_POST['param1'], 'date'=>date('Y-m-d')));
    $rating = $regs[1];
  }

  if ($receipt && $_POST['target'] != 'ajax')
  {
    $_POST['target'] = $_POST['target'] . ((strpos($_POST['target'], '?')===FALSE)?'?':'&') . 'receipt='.$receipt->Get('id');
  }

  if ($_POST['target'] == 'ajax')
  {
    if($receipt && $receipt->IsValid())
    {
      header('Content-type: text/xml');
      echo '<?xml version="1.0" ?><receipt><id>'.$receipt->Get('id')."</id></receipt>\n";
    }
    exit(0);
  } 
 else
    header("Location: ".$_POST['target']);
?>
