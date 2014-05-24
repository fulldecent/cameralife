<?php
/**
 * Displays post installation notifcation messages
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */

$continue = true;
if (file_exists('../modules/config.inc')) {
    die("Camera Life already appears to be set up, because modules/config.inc exists.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Install Camera Life</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-52764-13']);
        _gaq.push(['_trackPageview']);

        (function () {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();
    </script>
</head>

<body>
<nav class="navbar navbar-default" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <span class="navbar-brand">INSTALL CAMERA LIFE</span>
        </div>
        <ul class="nav navbar-nav">
            <li><a>1. Login</a></li>
            <li class="active"><a>2. Setup</a></li>
            <li><a>3. Use Camera Life</a></li>
        </ul>
        <a class="btn btn-default navbar-btn pull-right" href="mailto:cameralifesupport@phor.net">
            <i class="glyphicon glyphicon-envelope"></i>
            Email support
        </a>
        <a class="btn btn-default navbar-btn pull-right" href="http://fulldecent.github.com/cameralife">
            <i class="glyphicon glyphicon-home"></i>
            Camera Life project page
        </a>
    </div>
</nav>

<div class="jumbotron">
    <div class="container">
        <h2>Installing to database</h2>
    </div>
</div>

<div class="container">
<?php
if (!$_POST['host']) {
    die ("You didn't specify a server to connect to, <a href=\"index.php\">go back</a> and try again");
}
if (!$_POST['name']) {
    die ("You didn't specify a database, <a href=\"index.php\">go back</a> and try again");
}
if (!$_POST['user']) {
    die ("You didn't specify a username, <a href=\"index.php\">go back</a> and try again");
}
if (!$_POST['pass']) {
    die ("You didn't specify a password, <a href=\"index.php\">go back</a> and try again");
}
$prefix = $_POST['prefix'];

$setupLink = @mysql_connect($_POST['host'], $_POST['user'], $_POST['pass'])
    or die ("I couldn't connect using those credentials, <a href=\"index2.php\">go back</a> and try again");

@mysql_select_db($_POST['name'], $setupLink)
    or die ("I couldn't select that database, <a href=\"index2.php\">go back</a> and try again");

$result = mysql_query(
    'SHOW TABLES FROM ' . $_POST['name'] . ' WHERE tables_in_' . $_POST['name'] . ' LIKE "' . $_POST['prefix'] . '%"',
    $setupLink
);
if (mysql_fetch_array($result)) {
    die ("The database " . $_POST['name'] . " has tables in it. The installer will not change
            the existing tables! To upgrade, consult the <a href='../UPGRADE'>UPGRADE</a> file");
}
?>

<h3>Logged in to database...</h3>

<?php

$SQL = "
      CREATE TABLE `${prefix}albums` (
        `id` int(11) NOT NULL auto_increment,
        `topic` varchar(20) NOT NULL default '',
        `name` varchar(25) NOT NULL default '',
        `term` varchar(20) NOT NULL default '',
        `poster_id` int(11) NOT NULL default '0',
        `hits` bigint(20) NOT NULL default '0',
        PRIMARY KEY  (`id`)
      );";
mysql_query($SQL)
or die(mysql_error() . ' ' . __LINE__);

$SQL = "
      CREATE TABLE `${prefix}photos` (
        `id` int(11) NOT NULL auto_increment,
        `filename` varchar(255) NOT NULL default '',
        `path` varchar(255) NOT NULL default '',
        `description` varchar(255) NOT NULL default '',
        `keywords` varchar(255) NOT NULL default '',
        `username` varchar(30) default NULL,
        `status` int(11) NOT NULL default '0',
        `flag` enum('indecent','photography','subject','bracketing') default NULL,
        `width` int(11) default '0',
        `height` int(11) default '0',
        `tn_width` int(11) default '0',
        `tn_height` int(11) default '0',
        `hits` bigint(20) NOT NULL default '0',
        `created` date default NULL,
        `fsize` bigint(20) NOT NULL default '0',
        `mtime` bigint(20) NOT NULL default '0',
        `modified` int(1) NOT NULL default '0',
        PRIMARY KEY  (`id`),
        UNIQUE KEY `path` (`path`, `filename`)
      );";
mysql_query($SQL)
or die(mysql_error() . ' ' . __LINE__);

$SQL = "
      CREATE TABLE `${prefix}ratings` (
        `id` int(11) NOT NULL,
        `username` varchar(30) default NULL,
        `user_ip` varchar(16) NOT NULL,
        `rating` int(11) NOT NULL,
        `date` datetime NOT NULL,
        UNIQUE KEY `id_3` (`id`,`username`,`user_ip`),
        KEY `id` (`id`),
        KEY `id_2` (`id`,`username`,`user_ip`),
        KEY `id_4` (`id`)
      );";
mysql_query($SQL)
or die(mysql_error() . ' ' . __LINE__);

$SQL = "
      CREATE TABLE `${prefix}comments` (
        `id` int(11) NOT NULL auto_increment,
        `photo_id` int(11) NOT NULL,
        `username` varchar(30) NOT NULL,
        `user_ip` varchar(16) NOT NULL,
        `comment` varchar(255) NOT NULL,
        `date` datetime NOT NULL,
        PRIMARY KEY  (`id`),
        KEY `id` (`photo_id`)
      );";
mysql_query($SQL)
or die(mysql_error() . ' ' . __LINE__);

$SQL = "
      CREATE TABLE `${prefix}preferences` (
        `prefmodule` varchar(64) NOT NULL default 'core',
        `prefkey` varchar(64) NOT NULL default '',
        `prefvalue` varchar(255) NOT NULL default '',
        `prefdefault` varchar(255) NOT NULL default '',
        PRIMARY KEY  (`prefmodule`,`prefkey`)
      );";
mysql_query($SQL)
or die(mysql_error() . ' ' . __LINE__);

mysql_query("INSERT INTO `${prefix}preferences` VALUES('CameraLife','sitedate',NOW(),NOW())")
or die(mysql_error() . ' ' . __LINE__);

$SQL = "
      CREATE TABLE `${prefix}users` (
        `id` int(10) NOT NULL auto_increment,
        `username` varchar(30) NOT NULL default '',
        `password` varchar(255) NOT NULL default '',
        `auth` int(11) NOT NULL default '0',
        `cookie` varchar(64) NOT NULL default '',
        `last_online` date NOT NULL default '0000-00-00',
        `last_ip` varchar(20) default NULL,
        `email` varchar(80) default NULL,
        PRIMARY KEY  (`username`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `id` (`id`)
      );";
mysql_query($SQL)
or die(mysql_error() . ' ' . __LINE__);

$SQL = "
      CREATE TABLE `${prefix}exif` (
        `photoid` int(11) NOT NULL,
        `tag` varchar(50) NOT NULL,
        `value` varchar(255) NOT NULL,
        PRIMARY KEY  (`photoid`,`tag`),
        KEY `photoid` (`photoid`)
      );";
mysql_query($SQL)
or die(mysql_error() . ' ' . __LINE__);

$SQL = "
      CREATE TABLE `${prefix}logs` (
        `id` int(11) NOT NULL auto_increment,
        `record_type` enum('album','photo','preference','user') NOT NULL default 'album',
        `record_id` int(11) NOT NULL default '0',
        `value_field` varchar(40) NOT NULL default '',
        `value_new` text NOT NULL,
        `user_name` varchar(30) NOT NULL default '',
        `user_ip` varchar(16) NOT NULL default '',
        `user_date` date NOT NULL default '0000-00-00',
        PRIMARY KEY  (`id`)
      );";
mysql_query($SQL)
or die(mysql_error() . ' ' . __LINE__);

echo "<p>Creating tables...</p>";

session_start();
$SQL = "INSERT INTO ${prefix}users (username, password, auth, cookie, last_online)
            VALUES ('" . mysql_real_escape_string($_SESSION['openid_email']) . "','" . mysql_real_escape_string(
        $_SESSION['openid_identity']
    ) . "',5,'" . $HTTP_SERVER_VARS['REMOTE_ADDR'] . "',NOW())";
mysql_query($SQL)
or die(mysql_error() . ' ' . __LINE__);

echo "<p>Creating admin account</p>";

?>

<h3>Writing configuration file</h3>

<?php
$config[] = "<?php\n";
$config[] = "\$db_host = '" . $_POST['host'] . "';\n";
$config[] = "\$db_name = '" . $_POST['name'] . "';\n";
$config[] = "\$db_user = '" . $_POST['user'] . "';\n";
$config[] = "\$db_pass = '" . $_POST['pass'] . "';\n";
$config[] = "\$db_prefix = '" . $_POST['prefix'] . "';\n";
$config[] = "\$db_schema_version = 4;\n";
$config[] = "?>\n";

if ($fd = fopen('../modules/config.inc', 'x')) {
    foreach ($config as $line) {
        fwrite($fd, $line);
    }
    fclose($fd);
    echo "<p>Writing configuration file...</p>";
    echo "<p>Configuration is complete.</p>";
} else {
    echo "<p>I cannot write your config file modules/config.inc ";
    echo "Please create this file and paste in the following:<pre class='code'>";
    foreach ($config as $line) {
        echo htmlentities($line) . "\n";
    }
    echo "</pre></p>";
}
?>

<a class="btn btn-primary btn-lg" href="index4.php"><i class="glyphicon glyphicon-arrow-right"></i> Continue</a>
</div>
<!-- /container -->
</body>
</html>
