<?php
/**
 * Accepts install parameters and perform the actual CL install
 *
 *@link http://fdcl.sourceforge.net
 *@version 2.6.2
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 */

  // Pretend like the user will authenticate by giving them a cookie.
  setcookie("cameralifeauth",$HTTP_SERVER_VARS['REMOTE_ADDR'],time()+3600, '/');
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="common.css">
<title>Camera Life Installation</title>
</head>
<body>

<h1>Setting up Camera Life</h1>

<h2>Setting up database</h2>

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

    $result = mysql_query('SHOW TABLES FROM '.$_POST['name'].' WHERE tables_in_'.$_POST['name'].' LIKE "'.$_POST['prefix'].'%"',$setup_link);
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

    mysql_query("INSERT INTO `${prefix}preferences` VALUES('CameraLife','sitedate',NOW(),NOW())")
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

<h2>Writing configuration file</h2>

  <?php
    $config[] = "<?php\n";
    $config[] = "\$db_host = '".$_POST['host']."';\n";
    $config[] = "\$db_name = '".$_POST['name']."';\n";
    $config[] = "\$db_user = '".$_POST['user']."';\n";
    $config[] = "\$db_pass = '".$_POST['pass']."';\n";
    $config[] = "\$db_prefix = '".$_POST['prefix']."';\n";
    $config[] = "?>\n";

    if ($fd = fopen('../modules/config.inc','x'))
    {
      foreach ($config as $line)
        fwrite ($fd, $line);
      fclose($fd);

      echo "<p>Writing configuration file...</p>";
      echo "<p>Configuration is complete.</p>";
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

<h2>Setting up .htaccess</h2>

<?php
  $htaccess = file('../.htaccess');
  if (!$htaccess)
    $htaccess = file('example.htaccess');
  if (!$htaccess)
    die('Serious error, could not read htaccess file from setup directory or base directory');

  $fixed = 0;
  $dir = dirname(dirname($_SERVER['PHP_SELF']));
  $dir = trim($dir, '/');
  $newht = preg_replace('/RewriteBase .*/',"RewriteBase /$dir/",$htaccess,1,$fixed);

  if ($fd = fopen('../.htaccess','w+'))
  {
    foreach ($newht as $line)
      fwrite ($fd, $line);
    fclose($fd);

    echo "<p>Writing .htaccess file...</p>";
    echo "<p>.htaccess is complete.</p>";
  }
  else
  {
    echo "<p>I cannot write your ".dirname(dirname(__FILE__))."/.htaccess file. ";
    echo "Please create this file and paste in the following:<pre class='code'>";
    foreach ($newht as $line)
      echo htmlentities($line) . "\n";
    echo "</pre></p>";
  }

?>



  <p align=center>
  <a class="pagelink" href="index3.php">Continue --&gt;</a>
  </p>

</body>
</html>
