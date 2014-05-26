<?php
namespace CameraLife;

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
     * @param mixed $db_host
     * @param mixed $db_name
     * @param mixed $db_user
     * @param mixed $db_pass
     * @param mixed $db_prefix
     * @return mixed true for succes, string for failure
     */
    public function canUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix)
    {
        $this->link = @mysql_connect($db_host, $db_user, $db_pass);
        if (!$this->link) {
            return "I couldn't connect to the database using credentials in modules/config.inc";
        }

        $select = @mysql_select_db($db_name, $this->link);
        if (!$select) {
            return "I couldn't connect to the database listed in modules/config.inc";
        }

        $sql = "SHOW TABLES FROM $db_name WHERE tables_in_$db_name LIKE '{$db_prefix}%'";
        $result = mysql_query($sql, $this->link);
        $array = mysql_fetch_array($result);
        if (!$array) {
            return "The database $db_name does not have tables in it. I don't know how you got to the
              upgrade utility, but it looks like you want the installer utility.";
        }

        return true;
    }

    public function doUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix)
    {
        $can = $this->canUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix);
        if ($can !== true) {
            return $can;
        }

        $sql = "ALTER TABLE ${db_prefix}photos ADD UNIQUE INDEX (filename, path);";
        $result = mysql_query($sql, $this->link);
        if (!$result) {
            return "Update script failed: " . mysql_error();
        }

        $sql = "UPDATE ${db_prefix}photos SET path = mid(path,2) WHERE path LIKE '/%';";
        $result = mysql_query($sql, $this->link);
        if (!$result) {
            return "Update script failed: " . mysql_error();
        }

        $sql = "UPDATE ${db_prefix}photos SET path = mid(path,1,length(path)-1) WHERE path LIKE '%/';";
        $result = mysql_query($sql, $this->link);
        if (!$result) {
            return "Update script failed: " . mysql_error();
        }

        $sql = "UPDATE ${db_prefix}photos SET path = concat('/',path);";
        $result = mysql_query($sql, $this->link);
        if (!$result) {
            return "Update script failed: " . mysql_error();
        }

        return true;
    }
}
