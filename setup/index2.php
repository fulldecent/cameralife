<?php
  // Pretend like the user will authenticate by giving them a cookie.
  /**This file accepts information from user and uses the information to
  *<ul>
  *<li>Connect to the database</li>
  *<li>Create tables</li>
  *<li>Setup CameraLife </li>
  *</ul>
  *<b>Note</b> The user will "authenticate"the provided information by enabling a cookie.
  *@link http://fdcl.sourceforge.net
*@version 2.6.2
*@author Will Entriken <cameralife@phor.net>
*@copyright © 2001-2009 Will Entriken
*@access public
*/
/**
*/


  setcookie("cameralifeauth",$HTTP_SERVER_VARS['REMOTE_ADDR'],time()+3600, '/');
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="common.css">
<title>Camera Life Installation</title>
</head>
<body>

<h1>Database Setup</h1>

<?php if (!$_POST) { ?>

  <h2>Create a database</h2>

  We need a database to run. Here are instructions for setting that up, make substitutions as necessary.

  <p>For Linux:</p>

  <pre class="code">
  $ su
  # mysqladmin create <b>cameralife</b>
  # mysql
  mysql&lt; grant all privileges on <b>cameralife</b>.* to <b>cameralifeuser</b>@<b>localhost</b> identified by '<b>password</b>';
  </pre>

  <p>Using cPanel:</p>

  <ul>
    <li><a target="_new" href="http://<?= $_SERVER['HTTP_HOST'] ?>/cpanel">Login to cPanel</a></li>
    <li>Click <a target="_new" href="http://<?= $_SERVER['HTTP_HOST'] ?>:2082/frontend/x3/sql/index.html">MySQL Databases</a></li>
    <li>Create Database: cameralife, go back</li>
    <li>Add New User: <b>username</b> <b>password</b></li>
    <li>Add User To Database: select your user and database, and tick ALL PRIVILEGES</li>
    <li>Note, your cPanel account name will proceed your database and user names below. For example, your database name will be mycpanelname_cameralife</li>
  </ul>

  <p>Note: If you setup Camera Life on a different system, please tell us about it at cameralife@phor.net</p>

  <h2>Initialize database</h2>

  Provide the credentials you chose above so Camera Life may access your new database. Also choose the admin password for your new website.

  <br><br>

  <table>
  <form action="index2.php" method=POST>
  <tr><td>Database server:<td> <input type="text" name="host" value="localhost">
  <tr><td>Database name:<td> <input type="text" name="name" value="cameralife">
  <tr><td>Database user:<td> <input type="text" name="user" value="cameralifeuser">
  <tr><td>Database pass:<td> <input type="password" name="pass" value="">
  <tr><td>Database table name prefix (optional):<td> <input type="text" name="prefix" value="">
  <tr><td>&nbsp;
  <tr><td>Camera Life admin password:<td> <input type="password" name="sitepass" value="">
  </table>

<?php
    if (!is_writable('../modules'))
    {
      echo "<p class='important'>If you make the folder modules/ writable by your webserver, I can set up your configuration file for you, otherwise you'll need to edit it later.</p>";
    }
?>
  <center>
    <input class="pagelink" type="submit" value="Continue --&gt;">
  </center>
  </form>

<?php } else { ?>

  <?php
    if (!$_POST['host'])
      die ("You didn't specify a server to connect to, <a href=\"index2.php\">go back</a> and try again");
    if (!$_POST['name'])
      die ("You didn't specify a database, <a href=\"index2.php\">go back</a> and try again");
    if (!$_POST['user'])
      die ("You didn't specify a username, <a href=\"index2.php\">go back</a> and try again");
    if (!$_POST['pass'])
      die ("You didn't specify a password, <a href=\"index2.php\">go back</a> and try again");
    if (!$_POST['sitepass'])
      die ("You didn't specify a site password, <a href=\"index2.php\">go back</a> and try again");
    $prefix = $_POST['prefix'];

    $setup_link = @mysql_connect($_POST['host'],$_POST['user'],$_POST['pass'])
      or die ("I couldn't connect using those credentials, <a href=\"index2.php\">go back</a> and try again");

    @mysql_select_db($_POST['name'], $setup_link)
      or die ("I couldn't select that database, <a href=\"index2.php\">go back</a> and try again");

    $result = mysql_query('SHOW TABLES FROM '.$_POST['name'],$setup_link);
    if (mysql_fetch_array($result))
      die ("The database ".$_POST['name']." has tables in it. The installer will not change
            the existing tables! To upgrade, consult the <a href='../UPGRADE'>UPGRADE</a> file");
  ?>

  <p>Logged in to database...</p>

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
      ) TYPE=MyISAM COMMENT='Sections of pictures';";
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
        UNIQUE KEY `id` (`id`)
      ) TYPE=MyISAM COMMENT='Photos and their descriptions';";
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
      ) TYPE=MyISAM COMMENT='Photo Ratings';";
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
      ) TYPE=MyISAM COMMENT='Photo comments';";
    mysql_query($SQL)
      or die(mysql_error() . ' ' . __LINE__);

    $SQL = "
      CREATE TABLE `${prefix}preferences` (
        `prefmodule` varchar(64) NOT NULL default 'core',
        `prefkey` varchar(64) NOT NULL default '',
        `prefvalue` varchar(255) NOT NULL default '',
        `prefdefault` varchar(255) NOT NULL default '',
        PRIMARY KEY  (`prefmodule`,`prefkey`)
      ) TYPE=MyISAM COMMENT='Customizable site options';";
    mysql_query($SQL)
      or die(mysql_error() . ' ' . __LINE__);

    mysql_query("INSERT INTO `${prefix}preferences` VALUES('core','sitedate',NOW(),NOW())")
      or die(mysql_error() . ' ' . __LINE__);

    $SQL = "
      CREATE TABLE `${prefix}users` (
        `username` varchar(30) NOT NULL default '',
        `password` varchar(64) NOT NULL default '',
        `auth` int(11) NOT NULL default '0',
        `cookie` varchar(64) NOT NULL default '',
        `last_online` date NOT NULL default '0000-00-00',
        `last_ip` varchar(20) default NULL,
        `email` varchar(80) default NULL,
        PRIMARY KEY  (`username`),
        UNIQUE KEY `username` (`username`)
      ) TYPE=MyISAM COMMENT='Users of the system';";
    mysql_query($SQL)
      or die(mysql_error() . ' ' . __LINE__);

    $SQL = "
      CREATE TABLE `${prefix}exif` (
        `photoid` int(11) NOT NULL,
        `tag` varchar(50) NOT NULL,
        `value` varchar(255) NOT NULL,
        PRIMARY KEY  (`photoid`,`tag`),
        KEY `photoid` (`photoid`)
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
    mysql_query($SQL)
      or die(mysql_error() . ' ' . __LINE__);

    $SQL = "
      CREATE TABLE `${prefix}logs` (
        `id` int(11) NOT NULL auto_increment,
        `record_type` enum('album','photo','preference','user') NOT NULL default 'album',
        `record_id` int(11) NOT NULL default '0',
        `value_field` varchar(40) NOT NULL default '',
        `value_old` text NOT NULL,
        `value_new` text NOT NULL,
        `user_name` varchar(30) NOT NULL default '',
        `user_ip` varchar(16) NOT NULL default '',
        `user_date` date NOT NULL default '0000-00-00',
        PRIMARY KEY  (`id`)
      ) TYPE=MyISAM COMMENT='Logs modifications to the system';";
    mysql_query($SQL)
      or die(mysql_error() . ' ' . __LINE__);

    echo "<p>Creating tables...</p>";

    $salted_password = crypt($_POST['sitepass'],'admin');
    $SQL = "INSERT INTO ${prefix}users (username, password, auth, cookie, last_online)
            VALUES ('admin','$salted_password',5,'".$HTTP_SERVER_VARS['REMOTE_ADDR']."',NOW())";
    mysql_query($SQL)
      or die(mysql_error() . ' ' . __LINE__);

    echo "<p>Creating admin account</p>";

  ?>

  <?php
    $config[] = "<?php\n";
    $config[] = "\$db_host = '".$_POST['host']."';\n";
    $config[] = "\$db_name = '".$_POST['name']."';\n";
    $config[] = "\$db_user = '".$_POST['user']."';\n";
    $config[] = "\$db_pass = '".$_POST['pass']."';\n";
    $config[] = "\$db_prefix = '".$_POST['prefix']."';\n";
    $config[] = "?>\n";

    if (is_writable('../modules'))
    {
      $fd = fopen('../modules/config.inc','x')
        or die('Cannot open ../modules/config.inc for writing');
      foreach ($config as $line)
        fwrite ($fd, $line);
      fclose($fd);

      echo "<p>Writing configuration file...</p>";
      echo "<p>Setup is complete.</p>";
    }
    else
    {
      echo "<p>I cannot write your config file modules/config.inc ";
      echo "Please create this file and paste in the following:<pre class='code'>";
      foreach ($config as $line)
        echo htmlentities($line) . "\n";
      echo "</pre></p>";
    }
  ?>

  <p align=center>
  <a class="pagelink" href="index3.php">Continue --&gt;</a>
  </p>

<?php } ?>

</body>
</html>
