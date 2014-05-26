<?php
class SchemaUpdater2 extends SchemaUpdater
{
    private $link;

    public function __construct()
    {
        $this->scriptInfo = <<<HERE
This will make the following changes:
<ul>
  <li>Increase length of <code>user.password</code> field to accomodate OpenID login.</li>
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

        $sql = "DESC {$db_prefix}users id";
        $result = mysql_query($sql, $this->link);
        $array = mysql_fetch_array($result);
        if ($array) {
            return "The database $db_name is already running a db schema version at or greater than 2.
              Please check that modules/config.inc includes \$db_schema_version = XXX, where XXX is your current schema version.";
        }

        return true;
    }

    public function doUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix)
    {
        $can = $this->canUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix);
        if ($can !== true) {
            return $can;
        }

        $sql = "ALTER TABLE ${db_prefit}users MODIFY COLUMN password varchar(255) NOT NULL;";
        $result = mysql_query($sql, $this->link);
        if (!$result) {
            return "Update script failed: " . mysql_error();
        }

        $sql = "ALTER TABLE ${db_prefit}users ADD COLUMN id INT(10) NOT NULL AUTO_INCREMENT UNIQUE FIRST;";
        $result = mysql_query($sql, $this->link);
        if (!$result) {
            return "Update script failed: " . mysql_error();
        }

        return true;
    }
}
