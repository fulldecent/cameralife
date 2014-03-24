<?php

# Upgrade database schema

if (file_exists(dirname(dirname(dirname(__FILE__))) . '/modules/config.inc')) {
    require(dirname(dirname(dirname(__FILE__))) . '/modules/config.inc');
} else {
    die('Cannot find /modules/config.inc. Upgrade is not possible.');
}

$installed_version = isset($db_schema_version) ? $db_schema_version : 0;
$latest_version = 4;

?>
<html>
<head>
    <title>Camera Life Database Updater Utility</title>
    <style type="text/css">body {
            width: 960px;
            background: #eee;
            margin: 2em auto
        } </style>
</head>
<body>
<h1>Camera Life Database Updater Utility</h1>

<p>The installed version of the database schema is <?php echo $installed_version ?>. The needed version
    is <?php echo $latest_version ?>.

    <?php
    if ($installed_version >= $latest_version) {
        echo "<p style=\"color:green\">No upgrade is necessary. Return to the <a href=\"../../\">main page</a>.</p>";
    } else {
        foreach (glob(dirname(__FILE__) . '/*.inc') as $script) {
            $a = basename($script, '.inc');
            if (is_numeric($a) && ($a > $installed_version) && ($a <= $latest_version)) {
                $scripts[] = $a;
            }
        }
        if (isset($scripts[0])) {
            require(dirname(__FILE__) . "/" . $scripts[0] . ".inc");

            if (isset($_GET['action']) && $_GET['action'] == $scripts[0]) {
                echo "<h2>Upgrade from db schema version $installed_version to " . $scripts[0] . "</h2>";
                $text = upgrade(); // this function set inside the script
                echo "<p>$text</p>";
            } else {
                echo "<h2>Upgrade from db schema version $installed_version to " . $scripts[0] . "</h2>";
                echo "<p>$script_info</p>";

                if (canupgrade()) { // this function set inside the script
                    echo "<form method=\"get\"><input type=\"hidden\" name=\"action\" value=\"" . $scripts[0] . "\"><input type=\"submit\" value=\"Upgrade to db schema version " . $scripts[0] . "\">";
                }
            }
        } else {
            echo "<p style=\"color:red\">No upgrade script is available.";
        }
    }

    ?>
</body>
</html>
