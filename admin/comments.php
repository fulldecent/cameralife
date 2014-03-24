<?php
/*
 * Administer comments on the site
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */
$features = array('security', 'fileStore');
require '../main.inc';
$cameralife->baseURL = dirname($cameralife->baseURL);
$cameralife->security->authorize('admin_customize', 1); // Require

if (!isset($_POST['showme']) && !isset($_POST['showreg']) && !isset($_POST['showunreg'])) {
    $_POST['showme'] = true;
    $_POST['showreg'] = true;
    $_POST['showunreg'] = true;
}
if (isset($_POST['action']) && $_POST['action'] == 'Delete checked') {
    foreach ($_POST as $var => $val) {
        if (!is_numeric($var) || !is_numeric($val)) {
            continue;
        }
        $cameralife->database->Delete('comments', "id=$var");
    }
}
$numcomments = $cameralife->database->SelectOne(
    'comments',
    'COUNT(*)',
    'id>' . ($cameralife->getPref('checkpointcomments') + 0)
);
$checkpointDate = strtotime(
    $cameralife->database->SelectOne('comments', 'max(date)', 'id=' . ($cameralife->getPref('checkpointcomments') + 0))
);
$latestComment = $cameralife->database->SelectOne('comments', 'max(id)');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Camera Life - Administration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
</head>

<body>

<div class="navbar navbar-inverse navbar-static-top">
    <div class="container">
        <span class="navbar-brand"><a href="../"><?= $cameralife->getPref("sitename") ?></a> / Administration</span>
    </div>
</div>
<div class="container">
    <h2>Options</h2>
    <?php
    if ($checkpointDate) {
        ?>
        <form class="alert alert-info" method="post" action="controller_prefs.php">
            A checkpoint was set on <?= date("Y-m-d", $checkpointDate) ?>. Only comments after then are shown.
            <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'] ?>"/>
            <input type="hidden" name="module1" value="CameraLife"/>
            <input type="hidden" name="param1" value="checkpointcomments"/>
            <input type="hidden" name="value1" value="0">
            <input class="btn btn-default" type="submit" value="Reset checkpoint">
            <a href="https://github.com/fulldecent/cameralife/wiki/Checkpoints" class="btn btn-default"><i
                    class="icon-info-sign"></i> Learn about checkpoints</a>
        </form>
    <?php
    } else {
        ?>
        <form class="alert alert-info" method="post" action="controller_prefs.php">
            No checkpoint is set. All comments are being shown.
            <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'] ?>"/>
            <input type="hidden" name="module1" value="CameraLife"/>
            <input type="hidden" name="param1" value="checkpointcomments"/>
            <input type="hidden" name="value1" value="<?= $latestComment ?>">
            <input class="btn btn-default" type="submit" value="Hide comments up to now">
            <a href="https://github.com/fulldecent/cameralife/wiki/Checkpoints" class="btn btn-default"><i
                    class="icon-info-sign"></i> Learn about checkpoints</a>
        </form>
    <?php
    }
    ?>
    <form class="well form-inline" method="post">
        Show comments from:
        <label class="checkbox inline">
            <input type="checkbox" name="showme" <?php if ($_POST["showme"]) {
                echo " checked";
} ?>>
            <i class="icon-user"></i> Me
        </label>
        <label class="checkbox inline">
            <input type="checkbox" name="showreg" <?php if ($_POST["showreg"]) {
                echo " checked";
} ?>>
            <i class="icon-user"></i> Registered users
        </label>
        <label class="checkbox inline">
            <input type="checkbox" name="showunreg" <?php if ($_POST["showunreg"]) {
                echo " checked";
} ?>>
            <i class="icon-user"></i> Unregistered users
        </label>
        <input class="btn btn-default" type=submit value="Update">
    </form>
    <div class="pull-right well">
        <h2>Quick tools</h2>
        <button class="btn btn-default" onclick="$('#comments :checkbox').slice(0,10).attr('checked',true)">Check the
            first 10 checkboxes
        </button>
        <br/>
        <button class="btn btn-default" onclick="$('#comments :checkbox').slice(0,50).attr('checked',true)">Check the
            first 50 checkboxes
        </button>
        <br/>
        <button class="btn btn-default" onclick="$('#comments :checkbox').slice(0,200).attr('checked',true)">Check the
            first 200 checkboxes
        </button>
        <br/>
        <button class="btn btn-default"
                onclick="$('#comments blockquote :contains(http://)').closest('label').children('input').attr('checked',true)">
            Check all with http://
        </button>
    </div>
    <h2>Comments</h2>

    <form method="post" class="form" id="comments">
        <?php
        $condition = "(0 ";
        if ($_POST['showme']) {
            $condition .= "OR username = '" . $cameralife->security->getName() . "' ";
        }
        if ($_POST['showreg']) {
            $condition .= "OR (username LIKE '_%' AND username != '" . $cameralife->security->getName() . "')";
        }
        if ($_POST['showunreg']) {
            $condition .= "OR username = '' ";
        }
        $condition .= ") ";

        $checkpoint = $cameralife->database->SelectOne('comments', 'MAX(id)');
        echo "<input type='hidden' name='checkpoint' value='$checkpoint'>\n";

        $condition .= " AND id > " . ($cameralife->getPref('checkpointcomments') + 0);
        $extra = "GROUP BY photo_id ORDER BY id DESC";

        $result = $cameralife->database->Select('comments', '*, MAX(id) as maxid', $condition, $extra);
        while ($record = $result->fetchAssoc()) {
            //var_dump($record);

            $photo = new Photo($record['photo_id']);
            $photoOpenGraph = $photo->getOpenGraph();
            $max = max($photoOpenGraph['og:image:width'], $photoOpenGraph['og:image:height']);
            $width64 = $photoOpenGraph['og:image:width'] / $max * 64;
            $height64 = $photoOpenGraph['og:image:height'] / $max * 64;
            ?>
            <div class="media">
                <a class="pull-left" style="width: 64px" href="<?= htmlspecialchars($photoOpenGraph['og:url']) ?>">
                    <img class="media-object" data-src="holder.js/64x64" alt="thumbnail"
                         style="width: <?= $width64 ?>px; height: <?= $height64 ?>px;"
                         src="<?= htmlspecialchars($photoOpenGraph['og:image']) ?>">
                </a>

                <div class="media-body">
                    <h4 class="media-heading"><?= htmlentities($photoOpenGraph['og:title']) ?></h4>
                    <?php
                    $condition = "photo_id = " . $record['photo_id'];
                    $result2 = $cameralife->database->Select('comments', '*', $condition, 'ORDER BY id DESC');

                    while ($row = $result2->fetchAssoc()) {
                        $byLine = ($row['username'] ? $row['username'] : 'Anonymous') . ' (' . $row['user_ip'] . ') ' . $row['date'];
                        ?>
                        <label class="checkbox">
                            <input type="checkbox" name="<?= $row['id'] ?>" value="<?= $row['id'] ?>">
                            <blockquote>
                                <p><?= htmlentities($row['comment']) ?></p>
                                <small><?= $byLine ?></small>
                            </blockquote>
                        </label>
                    <?php
                    }
                    ?>
                </div>
            </div>
        <?php
        }
        ?>
        <p>
            <input class="btn btn-danger" type=submit name="action" value="Delete checked">
            <a class="btn btn-default" href="?">Revert to last saved</a><br>
        </p>
    </form>
</div>
</body>
</html>
