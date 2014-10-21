<?php
namespace CameraLife;
/**
 * View for logging in or registering
 * @author William Entriken<cameralife@phor.net>
 * @access public
 * @copyright 2001-2009 William Entriken
 */

# $cameralife set by showPage()
$cameralife->getFeature('security');
$cameralife->getFeature('fileStore');
$cameralife->getFeature('security');

$_GET['page'] or $_GET['page'] = 'login';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <title><?= $cameralife->getPref('sitename') ?></title>
    <?php if ($location) {
        echo "<meta http-equiv='refresh' content='1;url=$location'>";
} ?>
    <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
</head>
<body>
<form method="post" action="login_controller.php">

    <?php
    /*$sections[] = array('name'=>'Login',
                          'page_name'=>'login',
                          'image'=>'small-login');
      $sections[] = array('name'=>'register',
                          'page_name'=>'register',
                          'image'=>'small-login');

      $cameralife->theme->multiSection($sections);
    */
    ?>
    <table>
        <tr>
            <td>Username:
            <td><input type="text" name="username" value="<?= $_POST["username"] ?>">
        <tr>
            <td>Password:
            <td><input type="password" name="password" value="">
                <?php
                if ($_GET['page'] == 'register') {
                    echo '<tr><td>E-Mail (optional):<td><input type="text" name="email" value="' . $_POST["email"] . '">';
                }
                ?>
    </table>

    <input type="hidden" name="target" value="<?= $cameralife->baseURL . '/index.php' ?>">
    <input type="submit" name="action" value="<?= htmlentities(ucwords($_GET['page'])) ?>">

</form>
</body>
</html>

