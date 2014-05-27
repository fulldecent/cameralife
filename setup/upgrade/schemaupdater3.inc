<?php
namespace CameraLife;

class SchemaUpdater3 extends SchemaUpdater
{
    private $link;

    public function __construct()
    {
        $this->scriptInfo = <<<HERE
This will make the following changes:
<ul>
  <li>Remove logs.value_old -- it is not necessary</li>
</ul>
HERE;
    }

    /**
     * Side effect: sets up $this->link
     *
     * @access public
     * @param mixed $dbHost
     * @param mixed $dbName
     * @param mixed $dbUser
     * @param mixed $dbPass
     * @param mixed $dbPrefix
     * @return mixed true for succes, string for failure
     */
    public function canUpgrade($dbHost, $dbName, $dbUser, $dbPass, $dbPrefix)
    {
        $this->link = @mysql_connect($dbHost, $dbUser, $dbPass);
        if (!$this->link) {
            return "I couldn't connect to the database using credentials in modules/config.inc";
        }

        $select = @mysql_select_db($dbName, $this->link);
        if (!$select) {
            return "I couldn't connect to the database listed in modules/config.inc";
        }

        $sql = "SHOW TABLES FROM $dbName WHERE tables_in_$dbName LIKE '{$dbPrefix}%'";
        $result = mysql_query($sql, $this->link);
        $array = mysql_fetch_array($result);
        if (!$array) {
            return "The database $dbName does not have tables in it. I don't know how you got to the
              upgrade utility, but it looks like you want the installer utility.";
        }

        $sql = "DESC {$dbPrefix}logs value_old";
        $result = mysql_query($sql, $this->link);
        $array = mysql_fetch_array($result);
        if (!$array) {
            return "The database $dbName is already running a db schema version at or greater than 2.
              Please check that modules/config.inc includes \$db_schema_version = XXX, where XXX is your current schema version.";
        }

        return true;
    }

    public function doUpgrade($dbHost, $dbName, $dbUser, $dbPass, $dbPrefix)
    {
        $can = $this->canUpgrade($dbHost, $dbName, $dbUser, $dbPass, $dbPrefix);
        if ($can !== true) {
            return $can;
        }

        $sql = "ALTER TABLE ${dbPrefix}logs DROP COLUMN value_old;";
        $result = mysql_query($sql, $this->link);
        if (!$result) {
            return "Update script failed: " . mysql_error();
        }
        return true;
    }
}
