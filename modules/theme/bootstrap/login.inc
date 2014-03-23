<?php
/**Enables
*<ul>
*<li>Registering to the site </li>
*<li>Log in</li>
*</ul>
*
*<b>Note</b> 
*<ul><li>It precedes the index page</li>
*<li>$cameralife and $album are set by showPage()</li></ul>
*@author Will Entriken<cameralife@phor.net>
*@access public
*@copyright Copyright (c) 2001-2009 Will Entriken
*/
/**
*
*
*
*/
  # $cameralife and $album are set by showPage()

  $cameralife->getFeature('security');
  $cameralife->getFeature('filestore');

  # Handles logging in and registering... then sends 'em to the index page

  $cameralife->getFeature('security');

  $_GET['page']
    or $_GET['page'] = 'login';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <?php if($location){echo "<meta http-equiv='refresh' content='1;url=$location'>";} ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
</head>
<body>
<form method="post" action="login_controller.php">

  <?php
/*$sections[] = array('name'=>'Login',
                      'page_name'=>'login',
                      'image'=>'small-login');
  $sections[] = array('name'=>'Register',
                      'page_name'=>'register',
                      'image'=>'small-login');

  $cameralife->Theme->MultiSection($sections);
*/
?>
<table>
  <tr><td>Username:<td><input type="text" name="param1" value="<?= $_POST["username"]?>">
  <tr><td>Password:<td><input type="password" name="param2" value="">
<?php
  if ($_GET['page'] == 'register')
    echo '<tr><td>E-Mail (optional):<td><input type="text" name="param3" value="'.$_POST["email"].'">';
?>
</table>

<input type="hidden" name="target" value="<?= $cameralife->base_url.'/index.php' ?>">
<input type="submit" name="action" value="<?= htmlentities(ucwords($_GET['page'])) ?>">

</form>
</body>
</html>

