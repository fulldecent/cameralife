<?php
/**A security feature that enables you log out of CameraLife
*@link http://fdcl.sourceforge.net
  *@version 2.6.3b3
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
*/
/**
*/

  $features=array('security');
  require "main.inc";

  $url = $cameralife->Security->Logout();

  if (is_string($url))
    header('Location: '.$url);
  else
    header('Location: '.$cameralife->base_url.'/index.php');
?>
