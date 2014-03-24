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
if (!isset($_POST['showphotos']) && !isset($_POST['showalbums']) && !isset($_POST['showusers']) && !isset($_POST['showpreferences'])) {
    $_POST['showphotos'] = true;
    $_POST['showalbums'] = true;
    $_POST['showusers'] = true;
    $_POST['showpreferences'] = true;
}
if (isset($_POST['action']) && $_POST['action'] == 'Commit changes') {
    foreach ($_POST as $var => $val) {
        if (!isset($var) || !is_numeric($val)) {
            continue;
        }
        AuditTrail::undo($val);
    }
}
$numcomments = $cameralife->database->SelectOne(
    'comments',
    'COUNT(*)',
    'id>' . ($cameralife->getPref('checkpointcomments') + 0)
);
$checkpointDate = strtotime(
    $cameralife->database->SelectOne('logs', 'max(user_date)', 'id=' . ($cameralife->getPref('checkpointcomments') + 0))
);
$latestLog = $cameralife->database->SelectOne('logs', 'max(id)');
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
        A checkpoint was set on <?= date("Y-m-d", $checkpointDate) ?>. Only logs after then are shown.
        <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'] ?>"/>
        <input type="hidden" name="module1" value="CameraLife"/>
        <input type="hidden" name="param1" value="checkpointlogs"/>
        <input type="hidden" name="value1" value="0">
        <input class="btn btn-default" type="submit" value="Reset checkpoint">
        <a href="https://github.com/fulldecent/cameralife/wiki/Checkpoints" class="btn btn-default"><i
                class="icon-info-sign"></i> Learn about checkpoints</a>
    </form>
<?php
} else {
    ?>
    <form class="alert alert-info" method="post" action="controller_prefs.php">
        No checkpoint is set. All logs are being shown.
        <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'] ?>"/>
        <input type="hidden" name="module1" value="CameraLife"/>
        <input type="hidden" name="param1" value="checkpointlogs"/>
        <input type="hidden" name="value1" value="<?= $latestLog ?>">
        <input class="btn btn-default" type="submit" value="Hide logs up to now">
        <a href="https://github.com/fulldecent/cameralife/wiki/Checkpoints" class="btn btn-default"><i
                class="icon-info-sign"></i> Learn about checkpoints</a>
    </form>
<?php
}
?>
<form class="well form-horizontal form-inline" method="post">
    <div class="control-group">
        <label class="control-label" for="inputEmail">Show comments from</label>

        <div class="controls">
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
                <i class="icon-user"></i> registered users
            </label>
            <label class="checkbox inline">
                <input type="checkbox" name="showunreg" <?php if ($_POST["showunreg"]) {
                    echo " checked";
} ?>>
                <i class="icon-user"></i> Unregistered users
            </label>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="inputPassword">Show change types</label>

        <div class="controls">
            <label class="checkbox inline">
                <input type="checkbox" name="showphotos" <?php if ($_POST["showphotos"]) {
                    echo " checked";
} ?>>
                <img src="<?= $cameralife->iconURL('small-photo') ?>"> Photos
            </label>
            <label class="checkbox inline">
                <input type="checkbox" name="showalbums" <?php if ($_POST["showalbums"]) {
                    echo " checked";
} ?>>
                <img src="<?= $cameralife->iconURL('small-album') ?>"> Albums
            </label>
            <label class="checkbox inline">
                <input type="checkbox" name="showusers" <?php if ($_POST["showusers"]) {
                    echo " checked";
} ?>>
                <img src="<?= $cameralife->iconURL('small-login') ?>"> Users
            </label>
            <label class="checkbox inline">
                <input type="checkbox"
                       name="showpreferences" <?php if ($_POST["showpreferences"]) {
                    echo " checked";
} ?>>
                <img src="<?= $cameralife->iconURL('small-admin') ?>"> Preferences
            </label>
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <input type="submit" class="btn btn-default" value="Update"/>
        </div>
    </div>
</form>
<div class="pull-right well">
    <h2>Quick tools</h2>
    <button class="btn btn-default"
            onClick='inps = document.getElementsByTagName("input"); for (a in inps) { b=inps[a]; if(b.type!="radio")continue; if(b.value=="") b.checked=true }; return false'>
        Set each item to the current value
    </button>
    <br/>
    <button class="btn btn-default"
            onClick='inps = document.getElementsByTagName("input"); c=0; for (a in inps) { b=inps[a]; if(b.type!="radio")continue; if(c)b.checked=true; c=(b.value=="")}; return false'>
        Set each item to the previous value
    </button>
    <br/>
    <button class="btn btn-default"
            onClick='inps = document.getElementsByTagName("input"); for (i=inps.length-1;i>=0;i--) { b=inps[i]; if(b.type!="radio")continue; b.checked=true }; return false'>
        Set each item to the oldest value
    </button>
    <br/>
</div>
<h2>Logged changes</h2>

<form method="post" class="form" id="comments">
    <?php
    $condition = "(0 ";
    if ($_POST['showphotos']) {
        $condition .= "OR record_type = 'photo' ";
    }
    if ($_POST['showalbums']) {
        $condition .= "OR record_type = 'album' ";
    }
    if ($_POST['showusers']) {
        $condition .= "OR record_type = 'user' ";
    }
    if ($_POST['showpreferences']) {
        $condition .= "OR record_type = 'preference' ";
    }

    $condition .= ") AND (0 ";
    if ($_POST['showme']) {
        $condition .= "OR user_name = '" . $cameralife->security->getName() . "' ";
    }
    if ($_POST['showreg']) {
        $condition .= "OR (user_name LIKE '_%' AND user_name != '" . $cameralife->security->getName() . "')";
    }
    if ($_POST['showunreg']) {
        $condition .= "OR user_name = '' ";
    }
    $condition .= ") ";

    $condition .= " AND logs.id > " . ($cameralife->getPref('checkpointlogs') + 0);
    $extra = "GROUP BY record_id, record_type, value_field ORDER BY logs.id DESC";

    $result = $cameralife->database->Select(
        'logs',
        'record_type, record_id, value_field, MAX(logs.id) as maxid',
        $condition,
        $extra
    );
    while ($record = $result->fetchAssoc()) {
        $receipt = new Receipt($record['maxid']);
        $object = $receipt->getObject();
        $openGraph = $object->GetOpenGraph();
        $max = max($openGraph['og:image:width'], $openGraph['og:image:height']);
        $width64 = $openGraph['og:image:width'] / $max * 64;
        $height64 = $openGraph['og:image:height'] / $max * 64;

        ?>
        <div class="media">
            <a class="pull-left" style="width: 64px" href="<?= htmlspecialchars($openGraph['og:url']) ?>">
                <img class="media-object" data-src="holder.js/64x64" alt="thumbnail"
                     style="width:<?= $width64 ?>px; height:<?= $height64 ?>px;"
                     src="<?= htmlspecialchars($openGraph['og:image']) ?>">
            </a>

            <div class="media-body">
                <h4 class="media-heading"><?= htmlentities($openGraph['og:title']) ?> (<?= $record['record_type'] ?>
                    )</h4>
                <?php
                $chain = $receipt->getChain();
                $arr = $chain[0]->GetOld();
                $oldValue = $arr['value'];
                $fromReceipt = $arr['fromReceipt'];
                echo '<label class="checkbox">';
                echo '<input type="radio" name="' . $record['maxid'] . '" value="' . $chain[0]->Get('id') . '"> ';
                if ($fromReceipt) {
                    echo htmlentities($oldValue) . ' <span class="label label-info"> ' . $receipt->get(
                        'value_field'
                    ) . ' from before checkpoint</span>';
                } else {
                    echo htmlentities($oldValue) . ' <span class="label"> default ' . $receipt->get(
                        'value_field'
                    ) . '</span>';
                }
                echo '</label>';
                for ($i = 0; $i < count($chain) - 1; $i++) {
                    ?>
                    <label class="checkbox">
                        <input type="radio" name="<?= $record['maxid'] ?>" value="<?= $chain[$i + 1]->Get('id') ?>">
                        <?= $chain[$i]->Get('value_new') ?> <span
                            class="label label-info">updated <?= $chain[$i]->Get('value_field') ?></span>
                    </label>
                <?php
                }
                echo '<label class="checkbox">';
                echo '<input type="radio" name="' . $record['maxid'] . '" checked> ';
                echo $chain[$i]->Get('value_new') . ' <span class="label label-success">current ' . $chain[$i]->Get(
                    'value_field'
                ) . '</span>';
                echo '</label>';
                ?>
            </div>
        </div>
    <?php
    }
    ?>
    <p>
        <input class="btn btn-danger" type=submit name="action" value="Commit changes">
        <a class="btn btn-default" href="?">Revert to last saved</a><br>
    </p>
</form>
</div>
</body>
</html>
