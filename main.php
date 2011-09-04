<?php

  # This is a hack to be compatible with Gallery 2 for:
  #   the remote API
/**
*This hack will be compatible with Gallery 2 for a remote API
*@link http://fdcl.sourceforge.net
*@version 
*@author Will Entriken <cameralife@phor.net>
*@copyright Copyright (c) 2001-2009 Will Entriken
*@access public
*/

/**
*/
  $old = $_POST;
  unset($_POST);
  $_POST = $old['g2_form'];
  $_POST['userfile_name'] = $old['g2_userfile_name'];
  $_FILES['userfile'] = $_FILES['g2_userfile'];


  require 'gallery_remote2.php';
?>
