<?php
namespace CameraLife;

# Upgrade database schema

if (file_exists(dirname(dirname(dirname(__FILE__))) . '/modules/config.inc')) {
    require(dirname(dirname(dirname(__FILE__)))) . '/modules/config.inc';
} else {
    die('Cannot find /modules/config.inc. Upgrade is not possible.');
}
define('CAMERALIFE_LATEST_SCHEMA_VERSION', '5');
$installed_version = isset($db_schema_version) ? intval($db_schema_version) : 0;
$nextUpgraderVersion = $db_schema_version + 1;
$nextUpgraderClass = 'CameraLife\SchemaUpdater' . $nextUpgraderVersion;
$nextUpgraderFile = './' . strtolower('SchemaUpdater' . $nextUpgraderVersion) . '.inc';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Camera Life Database Updater Utility</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">
    <div class="page-header">
        <h1>Camera Life Database Updater Utility</h1>

        <p class="lead">
            Your database schema version is <?php echo $installed_version ?>.
            The needed version is <?php echo CAMERALIFE_LATEST_SCHEMA_VERSION ?>.
        </p>
    </div>
    <?php
    if ($installed_version >= CAMERALIFE_LATEST_SCHEMA_VERSION) {
        echo '<p class="text-success">Return to the <a href="../../">main page</a>.</p>';
    } elseif (!file_exists($nextUpgraderFile)) {
        echo '<p class="lead text-danger">No upgrade script is available.</p>';
    } else {
        include_once$nextUpgraderFile;
        $upgrader = new $nextUpgraderClass;
        echo '<p class="lead">Upgrade to version ' . $nextUpgraderVersion . '</p>';
        echo '<blockquote class="lead">' . $upgrader->scriptInfo . '</blockquote>';
        $canUpgrade = $upgrader->canUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix);
        if ($canUpgrade === true && isset($_GET['continue'])) {
            $result = $upgrader->doUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix);
            if (true === $result) {
                echo '<p class="lead text-success">Upgrade complete ';
                echo 'Please update your <code>modules/config.inc</code> and add ';
                echo '<code>$db_schema_version = ' . $nextUpgraderVersion . ';</code> ';
                echo '<a href="../../" class="btn btn-primary btn-large">Continue</a><p>';
            } else {
                echo '<p class="text-success">Upgrade failed</p>';
            }
        } elseif ($canUpgrade === true) {
            echo '<a class="btn btn-primary btn-large" href="?continue=yes">Perform upgrade</a>';
        } else {
            echo '<p class="lead text-danger">Automatic upgrade not possible.</p>';
            echo '<blockquote class="lead">' . $canUpgrade . '</blockquote>';
        }
    }
    ?>
    </div>
</body>
</html>
