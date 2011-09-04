<?php
  /** Enables login to your EXISTING system
  *
  * Use this as a starting point to integrate with your existing CMS.
  */
  $features=array('database','security');
  require "../../../main.inc";
  $cameralife->base_url = dirname(dirname(dirname($cameralife->base_url)));

  if(isset($_GET['register']))
    $action = 'register';
  else
    $action = 'login';
?>

<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <link rel="stylesheet" href="../../../admin/admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body>

<h1><?php echo ucwords($action) ?></h1>
<form method="post" action="login_controller.php">
<input type="hidden" name="target" value="<?= $cameralife->base_url.'/index.php' ?>">
<table>
  <tr><td>Username:<td><input type="text" name="param1" value="<?= $_POST["username"]?>">
  <tr><td>Password:<td><input type="password" name="param2" value="">
<?php if ($action == 'register') { ?>
  <tr><td>Email:<td><input type="text" name="param3" value="">
  <tr><td><td>
    <input type="submit" name="action" value="Register">
<?php } else { ?>
  <tr><td><td>
    <input type="submit" name="action" value="Login">
  <tr><td><td>
    <a href="?register">Or create an account</a>
<?php } ?>
</table>


</form>
</body>
</html>

