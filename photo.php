<?php
/**
* @author Will Entriken <cameralife@phor.net>
* @copyright Copyright (c) 2001-2009 Will Entriken
* @access public
*/

$features=array('theme','security','filestore');
require 'main.inc';

if (!Photo::photoExists($_GET['id'])) {
  header("HTTP/1.0 404 Not Found");
  $cameralife->error("Photo #".($original+1)." not found.", __FILE__, __LINE__);
}

$photo = new Photo($_GET['id']);
if ($photo->get('status') != 0)
  $cameralife->security->authorize('admin_file', 'This file has been flagged or marked private');
$photo->set('hits', $photo->get('hits') + 1);

$photo->showPage();
