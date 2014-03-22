<?php
  # Handle all POST form actions from album.php
  #
  # Pass me variables:
  # id = the album id
  # action = the action to perform on the album
  # param1 = extra info
  # param2 = extra info...
  # param3 = extra info...
  # target = the exit URL, or 'ajax' for an ajax call
  /**
  *Handles all POST form actions from album.php
   *Pass the following variables
   *<ul><li>id = the album id</li>
  *<li>action = the action to be performed on the album</li>
  *<li>param1 = extra info</li>
  *<li>param2 = extra info</li>
  *<li>param3 = extra info</li>
  *<li>target = the exit URL, or 'ajax' for an ajax call</li></ul>
*@author Will Entriken <cameralife@phor.net>
*@copyright Copyright (c) 2001-2009 Will Entriken
*@access public
*/
/**
*/
  $features=array('security');
  require 'main.inc';

  if ($_POST['action'] != 'Create')
    $album = new Album($_POST['id'])
      or $cameralife->Error('this album does not exist');

  if ($_POST['action'] == "Create") {
    $cameralife->Security->authorize('admin_albums',1);

    $topic = $_POST['param1'];
    if ($topic == 'othertopic')
      $topic = $_POST['param2'];
    $name = $_POST['param3'];
    $term = $_POST['param4'];

    $name or $camerlife->Error("You must name the album");
    $term or $camerlife->Error("You must have a search term");
    $topic or $camerlife->Error("You must choose a topic for the album");

    $condition = "status=0 and lower(description) like lower('%".$term."%')";
    $query = $cameralife->Database->Select('photos','id',$condition);
    $result = $query->FetchAssoc();

    if ($result)
        $poster_id = $result['id'];
    else
        $cameralife->Error("There are no matching photos. Please create an album only after adding photos that can go in it.");

    $album_record = array('topic'=>$topic, 'name'=>$name, 'term'=>$term, 'poster_id'=>$poster_id);
    $newId = $cameralife->Database->Insert('albums',$album_record);

    if ($_POST['target'] != 'ajax')
      $_POST['target'] = $cameralife->baseURL.'/album.php?id='.$newId;
  } elseif ($_POST['action'] == "Update") {
    $cameralife->Security->authorize('admin_albums',1);

    $topic = $_POST['param2'];
    if ($topic == 'othertopic')
      $topic = $_POST['param3'];

    $album->Set('name', $_POST['param1']);
    $album->Set('topic', $topic);
  } elseif ($_POST['action'] == "Delete") {
    $cameralife->Security->authorize('admin_albums',1);
    $album->Erase();

    if ($_POST['target'] != 'ajax') {
      // Are there other albums in this topic?
      $total = $cameralife->Database->SelectOne('albums','COUNT(*)',"topic='".$album->Get('topic')."'");
      if ($total)
        $_POST['target'] = $cameralife->baseURL.'/topic.php?name='.$album->Get('topic');
      else
        $_POST['target'] = $cameralife->baseURL.'/index.php';
    }
  } elseif ($_POST['action'] == 'Poster') {
    $cameralife->Security->authorize('admin_albums',1);
    $album->Set('poster_id', $_POST['param1']);
  }

  if ($_POST['target'] == 'ajax')
    exit(0);
  else
    header("Location: ".$_POST['target']);
