<?php
/**
* @author Will Entriken <cameralife@phor.net>
* @copyright Copyright (c) 2001-2009 Will Entriken
* @access public
*/

$features=array('theme','security','filestore');
require 'main.inc';

if (!Photo::PhotoExists($_GET['id'])) {
  header("HTTP/1.0 404 Not Found");
  $cameralife->Error("Photo #".intval($_GET['id'])." not found.");
}

$photo = new Photo($_GET['id']);
if ($photo->Get('status') != 0)
  $cameralife->Security->authorize('admin_file', 'This file has been flagged or marked private');
$photo->Set('hits', $photo->Get('hits') + 1);

$photo->ShowPage();
