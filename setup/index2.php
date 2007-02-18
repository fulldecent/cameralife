<?php
  // Pretend like the user will authenticate by giving them a cookie.

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
  mysql> grant all privileges on <b>cameralife</b>.* to <b>cameralifeuser</b>@<b>localhost</b> identified by '<b>password</b>';
  </pre>

  <p>Using cPanel:</p>

  <pre>
  Click MySQL Databases

  Add Database: cameralife
  Add User: <b>username</b> <b>password</b>
  Add user to Db
  </pre>

  <p>Note: If you setup Camera Life on a different system, please tell me about it at cameralife@phor.net</p>

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
  <tr><td>Camera Life admin password:<td> <input type="password" name="fdclpass" value="">
  </table>

<?php
    if (!is_writable('../modules/database/mysql/config.inc'))
    {
      echo "<p class='important'>If you make the file modules/database/mysql/config.inc writable by your webserver, I can setup that file for you, otherwise you'll need to edit it later.</p>";
    }
?>
  <center>
    <input class="pagelink" type="image" src="images/continue.jpg" value="Go for it">
  </center>
  </form>

<?php } else { ?>

  <h2>Trying Credentials</h2>

  <?php
    if (!$_POST['host'])
      die ("You didn't specify a server to connect to, <a href=\"index2.php\">go back</a> and try again");
    if (!$_POST['name'])
      die ("You didn't specify a database, <a href=\"index2.php\">go back</a> and try again");
    if (!$_POST['user'])
      die ("You didn't specify a username, <a href=\"index2.php\">go back</a> and try again");
    if (!$_POST['pass'])
      die ("You didn't specify a password, <a href=\"index2.php\">go back</a> and try again");
    if (!$_POST['fdclpass'])
      die ("You didn't specify a site password, <a href=\"index2.php\">go back</a> and try again");
    $prefix = $_POST['prefix'];

    $setup_link = @mysql_connect($_POST['host'],$_POST['user'],$_POST['pass'])
      or die ("I couldn't connect using those credentials, <a href=\"index2.php\">go back</a> and try again");

    @mysql_select_db($_POST['name'], $setup_link)
      or die ("I couldn't select that database, <a href=\"index2.php\">go back</a> and try again");

    $result = mysql_query('SHOW TABLES FROM '.$_POST['name'],$setup_link);
    if (mysql_fetch_array($result))
      die ("The database ".$_POST['name']." has tables in it. The installer will not change
            the existing tables! To upgrade, consult the UPGRADE file");
  ?>

  I am able to login with those credentials.

  <h2>Setting up tables</h2>

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
      or die(mysql_error());

    $SQL = "
      CREATE TABLE `${prefix}photos` (
        `id` int(11) NOT NULL auto_increment,
        `filename` varchar(255) NOT NULL default '',
        `path` varchar(255) NOT NULL default '',
        `description` varchar(255) NOT NULL default '',
        `keywords` varchar(255) NOT NULL default '',
        `username` varchar(30) default NULL,
        `status` int(11) NOT NULL default '0',
        `width` int(11) NOT NULL default '0',
        `height` int(11) NOT NULL default '0',
        `tn_width` int(11) NOT NULL default '0',
        `tn_height` int(11) NOT NULL default '0',
        `hits` bigint(20) NOT NULL default '0',
        `created` date default NULL,
        `fsize` bigint(20) NOT NULL default '0',
        `mtime` bigint(20) NOT NULL default '0',
        `modified` int(1) NOT NULL default '0',
        PRIMARY KEY  (`id`),
        UNIQUE KEY `id` (`id`)
      ) TYPE=MyISAM COMMENT='Photos and their descriptions';";
    mysql_query($SQL)
      or die(mysql_error());

    $SQL = "
      CREATE TABLE `${prefix}preferences` (
        `prefmodule` varchar(64) NOT NULL default 'core',
        `prefkey` varchar(64) NOT NULL default '',
        `prefvalue` varchar(255) NOT NULL default '',
        `prefdefault` varchar(255) NOT NULL default '',
        PRIMARY KEY  (`prefmodule`,`prefkey`)
      ) TYPE=MyISAM COMMENT='Customizable site options';";
    mysql_query($SQL)
      or die(mysql_error());
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','sitename','My Photos','My Photos')");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','siteabbr','Home','Home')");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','owner_email','none@none.none','none@none.none')");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','sitedate',NOW(),'2000-01-01')");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','photo_dir','image/photos','image/photos')");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','scaled_dir','image/scaled','image/scaled')");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','thumbnail_dir','image/thumbnail','image/thumbnail')");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','modified_dir','image/modified','image/modified')");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','upload_dir','image/upload','image/upload')");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','deleted_dir','image/deleted','image/deleted')");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','main_thumbnails',1,1)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','main_thumbnails_n',4,4)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','main_topics',2,2)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','main_topics_n',3,3)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','main_folders',1,1)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','main_folders_n',5,5)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','theme','sidebar','sidebar')");
    mysql_query("INSERT into `${prefix}preferences` VALUES('core','checkpoint',0,0)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('defaultseurity','auth_photo_rename',0,0)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('defaultseurity','auth_photo_delete',0,0)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('defaultseurity','auth_photo_modify',3,3)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('defaultseurity','auth_admin_albums',4,4)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('defaultseurity','auth_photo_upload',1,1)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('defaultseurity','auth_admin_file',4,4)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('defaultseurity','auth_admin_theme',4,4)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('defaultseurity','auth_admin_customize',5,5)");
    mysql_query("INSERT into `${prefix}preferences` VALUES('defaultseurity','auth_cookie','cameralifeauth','cameralifeauth')");

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
      or die(mysql_error());

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
      or die(mysql_error());

    echo "Done creating tables<br>";

    mysql_query("INSERT INTO ${prefix}preferences (sitedate) VALUES (NOW())")
      or die(mysql_error());

    echo "Done setting default preferences<br>";

    $salted_password = crypt($_POST['fdclpass'],'admin');
    $SQL = "INSERT INTO ${prefix}users (username, password, auth, cookie, last_online)
            VALUES ('admin','$salted_password',5,'".$HTTP_SERVER_VARS['REMOTE_ADDR']."',NOW())";
    mysql_query($SQL)
      or die(mysql_error());

    echo "Done creating admin account<br>";

  ?>

  <h2>Creating config file</h2>

  <?php
    $config[] = "<?php\n";
    $config[] = "\$db_host = '".$_POST['host']."';\n";
    $config[] = "\$db_name = '".$_POST['name']."';\n";
    $config[] = "\$db_user = '".$_POST['user']."';\n";
    $config[] = "\$db_pass = '".$_POST['pass']."';\n";
    $config[] = "\$db_prefix = '".$_POST['prefix']."';\n";
    $config[] = "?>\n";

    if (is_writable('../modules/database/mysql/config.inc'))
    {
      $fd = fopen('../modules/database/mysql/config.inc','w')
        or die('Cannot open common.php for writing');
      foreach ($config as $line)
        fwrite ($fd, $line);
      fclose($fd);

      echo "I have setup your config file appropriately.";
    }
    else
    {
      echo "I cannot write your config file modules/database/mysql/config.inc ";
      echo "Please copy this information to the file:<br>";
      foreach ($config as $line)
        echo htmlentities($line) . "<br>";
    }

    if (file_exists('../notinstalled.txt'))
    {
      echo "<p class='important'>Delete the file notinstalled.txt, so your site will go live.</p>";
    }

  ?>

  <p align=center>
  <a class="pagelink" href="index3.php"><img src="images/continue.jpg" alt="continue"></a>
  </p>

<?php } ?>

</body>
</html>
