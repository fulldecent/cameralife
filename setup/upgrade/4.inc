<?php

$script_info = <<<HERE
This will make the following changes:
<ul>
  <li>set photos.path to have the format /path instead of path/</li>
  <li>Create an index on photos.path | photos.filename</li>
</ul>
HERE;

function canupgrade()
{
  global $db_host, $db_name, $db_user, $db_pass, $db_prefix;

  $setup_link = @mysql_connect($db_host, $db_user, $db_pass)
    or die ("I couldn't connect to the database using credentials in modules/config.inc");

  @mysql_select_db($db_name, $setup_link)
    or die ("I couldn't connect to the database listed in modules/config.inc");

  $sql = "SHOW TABLES FROM $db_name WHERE tables_in_$db_name LIKE '{$db_prefix}%'";
  $result = mysql_query($sql,$setup_link);
  if (mysql_fetch_array($result) == false)
    die ("The database $db_name does not have tables in it. I don't know how you got to the
          upgrade utility, but it looks like you want the installer utility.");

  return true;
}

function upgrade()
{
  global $db_host, $db_name, $db_user, $db_pass, $db_prefix;

  $setup_link = @mysql_connect($db_host, $db_user, $db_pass)
    or die ("I couldn't connect to the database using credentials in modules/config.inc");

  @mysql_select_db($db_name, $setup_link)
    or die ("I couldn't connect to the database listed in modules/config.inc");

  $sql = "SHOW TABLES FROM $db_name WHERE tables_in_$db_name LIKE '{$db_prefix}%'";
  $result = mysql_query($sql,$setup_link);
  if (mysql_fetch_array($result) == false)
    die ("The database $db_name does not have tables in it. I don't know how you got to the
          upgrade utility, but it looks like you want the installer utility.");

  $sql = "ALTER TABLE ${db_prefix}photos ADD UNIQUE INDEX (filename, path);";
  $result = mysql_query($sql,$setup_link)
    or die ("Update script failed: ".mysql_error());

  $sql = "UPDATE ${db_prefix}photos SET path = mid(path,2) WHERE path LIKE '/%';";
  $result = mysql_query($sql,$setup_link)
    or die ("Update script failed: ".mysql_error());

  $sql = "UPDATE ${db_prefix}photos SET path = mid(path,1,length(path)-1) WHERE path LIKE '%/';";
  $result = mysql_query($sql,$setup_link)
    or die ("Update script failed: ".mysql_error());

  $sql = "UPDATE ${db_prefix}photos SET path = concat('/',path);";
  $result = mysql_query($sql,$setup_link)
    or die ("Update script failed: ".mysql_error());

  return 'Upgrade to db schema version 4 was successful! Please update your modules/config.inc
          file and add $db_schema_version = 4;';
}
