<?php
namespace CameraLife;

/**
 * Administers user access priviliges
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */
require '../../../main.inc';
$features = array('security');
$cameralife = CameraLife::cameraLifeWithFeatures($features);
$cameralife->baseURL = dirname(dirname(dirname($cameralife->baseURL)));
$cameralife->security->authorize('admin_customize', 1); //require

$_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 'users';

foreach ($_POST as $key => $val) {
    if ($val == "delete") {
        $cameralife->database->Delete('users', "id='$key'");
    } else {
        $cameralife->database->Update('users', array('auth' => $val), "id='$key'");
    }
}
$cameralife->savePreferences();

function html_select_auth($param_name)
{
    global $cameralife;
    $tag = get_class($cameralife->security) . '|' . $param_name;
    $authLevels = array(
        0 => 'Anyone',
        1 => 'Unconfirmed registration',
        2 => 'Confirmed registration',
        3 => 'Privileged account',
        4 => 'Administrator',
        5 => 'Owner'
    );
    echo "      <select name=\"$tag\">\n";
    foreach ($authLevels as $authLevelNum => $authLevelName) {
        if ($cameralife->security->getPref($param_name) == $authLevelNum) {
            echo "  <option selected value=\"$authLevelNum\">$authLevelName</option>\n";
        } else {
            echo "  <option value=\"$authLevelNum\">$authLevelName</option>\n";
        }
    }
    echo "</select>\n";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $cameralife->getPref('sitename') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script language="javascript">
        function changeall() {
            var val = document.getElementById('status').value;
            var inputs = document.getElementsByTagName('select');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].value = val;
            }
        }
    </script>
</head>
<body>
<div class="navbar navbar-inverse navbar-static-top">
    <div class="container">
        <span class="navbar-brand"><a href="../"><?= $cameralife->getPref("sitename") ?></a> / Administration</span>
    </div>
</div>

<div class="container">

    <h1>Security Manager (openid)</h1>

    <ul class="nav nav-tabs">
        <?php
        foreach (array('users' => 'Users', 'policies' => 'Policies') as $id => $name) {
            $class = $_GET['page'] == $id ? 'active' : '';
            echo "        <li class=\"$class\"><a href=\"?page=$id\">$name</a></li>\n";
        }
        ?>
    </ul>

    <?php
    if ($_GET['page'] == 'users') {
    ?>
    <form method="post">
        <table class="table">
            <tr>
            <th width="16%">User
            <th width="16%">Group
            <th width="16%">Last online
            <th width="16%">IP Address
            <th width="10%">Actions
            <th width="10%">Uploads
            <th width="10%">Options
                <?php
                $result = $cameralife->database->Select('users', '*', null, 'ORDER BY auth desc');
                while ($curuser = $result->fetchAssoc()) {
                    $count_actions = $cameralife->database->SelectOne(
                        'logs',
                        'COUNT(*)',
                        "user_name='" . $curuser["username"] . "'"
                    );
                    $count_photos = $cameralife->database->SelectOne(
                        'photos',
                        'COUNT(*)',
                        "username='" . $curuser["username"] . "'"
                    );

                    echo "<tr><td>\n";
                    echo "<tr><td><i class=\"fa fa-user\"></i> \n";
                    echo $curuser["username"] . "\n";
                    echo "  <td><select name=\"" . $curuser["id"] . "\">\n";
                    if ($curuser["auth"] == 1) {
                        echo "      <option selected value=\"1\">1 - Unconfirmed</option>\n";
                    } else {
                        echo "      <option value=\"1\">1 - Unconfirmed</option>\n";
                    }
                    if ($curuser["auth"] == 2) {
                        echo "      <option selected value=\"2\">2 - Confirmed</option>\n";
                    } else {
                        echo "      <option value=\"2\">2 - Confirmed</option>\n";
                    }
                    if ($curuser["auth"] == 3) {
                        echo "      <option selected value=\"3\">3 - Privileged</option>\n";
                    } else {
                        echo "      <option value=\"3\">3 - Privileged</option>\n";
                    }
                    if ($curuser["auth"] == 4) {
                        echo "      <option selected value=\"4\">4 - Administrator</option>\n";
                    } else {
                        echo "      <option value=\"4\">4 - Administrator</option>\n";
                    }
                    if ($curuser["auth"] >= 5) {
                        echo "      <option selected value=\"5\">5 - Owner</option>\n";
                    } else {
                        echo "      <option value=\"5\">5 - Owner</option>\n";
                    }
                    echo "    </select>\n";
                    echo "  <td>" . $curuser["last_online"] . "\n";
                    echo "  <td>" . $curuser["last_ip"] . "\n";
                    echo "  <td>" . $count_actions . "\n";
                    echo "  <td>" . $count_photos . "\n";
                    echo "  <td align=middle>\n";
                    if ($curuser["auth"] >= 5) {
                        echo "    <input type=checkbox disabled name=\"" . $curuser["id"] . "\" value=\"delete\">\n";
                    } else {
                        echo "    <input type=checkbox name=\"" . $curuser["id"] . "\" value=\"delete\">";
                    }
                    echo "&nbsp;Delete\n";
                }
                ?>
        </table>

        <?php
    } elseif ($_GET['page'] == 'policies') {
?>
            <form method="post" action="<?= $cameralife->baseURL . '/admin/controller_prefs.php' ?>">
                <input type="hidden" name="target"
                       value="<?=
                        $cameralife->baseURL . '/modules/security/' . $cameralife->getPref(
                            'security'
                        ) . '/administer.php' ?>&#63;page=<?= $_GET['page'] ?>">

                <p class="lead">Permissions - <i>the minimum user class required to perform certain actions</i></p>
                <table class="table">
                    <tr>
                        <td>Edit photo descriptions</td>
                        <td><?php html_select_auth("auth_photo_rename") ?></td>
                    </tr>
                    <tr>
                        <td>Delete photos (can be easily restored in file manager)</td>
                        <td><?php html_select_auth("auth_photo_delete") ?></td>
                    </tr>
                    <tr>
                        <td>Upload photos</td>
                        <td><?php html_select_auth("auth_photo_upload") ?></td>
                    </tr>
                    <tr>
                        <td>Modify photos (rotate, crop, resize...)</td>
                        <td><?php html_select_auth("auth_photo_modify") ?></td>
                    </tr>
                    <tr>
                        <td>Change and add albums and topics</td>
                        <td><?php html_select_auth("auth_admin_albums") ?></td>
                    </tr>
                    <tr>
                        <td>Administer file manager</td>
                        <td><?php html_select_auth("auth_admin_file") ?></td>
                    <tr>
                        <td>Administer theme manager (effects entire site)</td>
                        <td><?php html_select_auth("auth_admin_theme") ?></td>
                    </tr>
                    <tr>
                        <td>Upper administation (users, customize, register...)</td>
                        <td><?php html_select_auth("auth_admin_customize") ?></td>
                    </tr>
                </table>
                <?php
    } ?>
            <p>
                <input type="submit" value="Commit Changes" class="btn btn-primary">
                <a href="users.php" class="btn">Revert to last saved</a>
            </p>

        </form>
</body>
</html>
