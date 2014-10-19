<?php
namespace CameraLife;
require_once './schemaupdater.inc';

class SchemaUpdater4 extends SchemaUpdater
{
    private $link;

    public function __construct()
    {
        $this->scriptInfo = <<<HERE
This will make the following changes:
<ul>
  <li>set photos.path to have the format /path instead of path/</li>
  <li>Create an index on photos.path | photos.filename</li>
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

        return true;
    }

    public function doUpgrade($dbHost, $dbName, $dbUser, $dbPass, $dbPrefix)
    {
        $can = $this->canUpgrade($dbHost, $dbName, $dbUser, $dbPass, $dbPrefix);
        if ($can !== true) {
            return $can;
        }

        $sql = "ALTER TABLE ${dbPrefix}photos ADD UNIQUE INDEX (filename, path);";
        $result = mysql_query($sql, $this->link);
        if (!$result) {
            return "Update script failed: " . mysql_error();
        }

        $sql = "UPDATE ${dbPrefix}photos SET path = mid(path,2) WHERE path LIKE '/%';";
        $result = mysql_query($sql, $this->link);
        if (!$result) {
            return "Update script failed: " . mysql_error();
        }

        $sql = "UPDATE ${dbPrefix}photos SET path = mid(path,1,length(path)-1) WHERE path LIKE '%/';";
        $result = mysql_query($sql, $this->link);
        if (!$result) {
            return "Update script failed: " . mysql_error();
        }

        $sql = "UPDATE ${dbPrefix}photos SET path = concat('/',path);";
        $result = mysql_query($sql, $this->link);
        if (!$result) {
            return "Update script failed: " . mysql_error();
        }

        return true;
    }
}
