<?php
/**
 * Enables login to your EXISTING system
 *
 * Use this as a starting point to integrate with your existing CMS.
 */
$features = array('security');
require '../../../main.inc';
$cameralife->baseURL = dirname(dirname(dirname($cameralife->baseURL)));

if (isset($_GET['register'])) {
    $action = 'register';
} else {
    $action = 'login';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $cameralife->getPref('sitename') ?></title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <meta charset="utf-8">
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1><?php echo ucwords($action) ?></h1>
    </div>

    <form class="form-inline" role="form" method="post" action="login_controller.php">
        <input type="hidden" name="target" value="<?= $cameralife->baseURL . '/index.php' ?>">

        <div class="form-group">
            <label class="sr-only" for="param1">Username</label>
            <input type="text" class="form-control" id="param1" name="param1"
                   value="<?= isset($_POST["username"]) ? $_POST["username"] : '' ?>" placeholder="Enter username">
        </div>
        <div class="form-group">
            <label class="sr-only" for="param2">Password</label>
            <input type="password" class="form-control" id="param2" name="param2" placeholder="Password">
        </div>
        <?php if ($action == 'register') {
?>
            <div class="form-group">
                <label class="sr-only" for="param3">Email</label>
                <input type="email" class="form-control" id="param3" name="param3" placeholder="Enter Email">
            </div>
            <button type="submit" class="btn btn-default">register</button>
        <?php
} else {
?>
            <button type="submit" class="btn btn-default">Login</button>
            <a href="?register">Or create an account</a>
        <?php
} ?>
    </form>
</body>
</html>
