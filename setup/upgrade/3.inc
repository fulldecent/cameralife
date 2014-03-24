<?php

$script_info = <<<HERE
This will make the following changes:
<ul>
  <li>Remove value_old from logs -- it is not necessary</li>
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
    $result = mysql_query($sql, $setup_link);
    if (mysql_fetch_array($result) == false) {
        die ("The database $db_name does not have tables in it. I don't know how you got to the
          upgrade utility, but it looks like you want the installer utility.");
    }

    $sql = "DESC {$db_prefix}logs value_old";
    $result = mysql_query($sql, $setup_link);
    if (mysql_fetch_array($result) == false) {
        die ("The database $db_name is already running a db schema version at or greater than 3.
          Please check that modules/config.inc includes \$db_schema_version = XXX, where XXX is your current schema version.");
    }

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
    $result = mysql_query($sql, $setup_link);
    if (mysql_fetch_array($result) == false) {
        die ("The database $db_name does not have tables in it. I don't know how you got to the
          upgrade utility, but it looks like you want the installer utility.");
    }

    $sql = "ALTER TABLE ${db_prefix}logs DROP COLUMN value_old;";
    $result = mysql_query($sql, $setup_link)
    or die ("Update script failed: " . mysql_error());

    return 'Upgrade to db schema version 3 was successful! Please update your modules/config.inc
          file and add $db_schema_version = 3;';
}
