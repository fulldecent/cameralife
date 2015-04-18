<?php
namespace CameraLife\Models;

/**
 * Updates the database schema
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2015 William Entriken
 * @access public
 */
class SchemaUpdater2 extends SchemaUpdater
{
    public $scriptInfo = <<<HERE
This will make the following changes:
<ul>
  <li>Increase length of <code>user.password</code> field to accomodate OpenID login.</li>
</ul>
HERE;
  
    private $link;

    /**
     * Side effect: sets up $this->link
     *
     * @access public
     * @return mixed true for succes, string for failure
     */
    public function canUpgrade()
    {
        $result = Database::run("SHOW TABLES");
        $hasTables = false;
        while ($table = $result->fetchAssoc()) {
            if (Database::$prefix == substr($table[0], 0, strlen(Database::$prefix))) {
                $hasTables = true;
            }
        }
        
        if (!$hasTables) {
            return "The database $dbName does not have tables in it. I don't know how you got to the
              upgrade utility, but it looks like you want the installer utility.";
        }
        
        $sql = "DESC ".(Database::$prefix)."users id";
        $result = Database::run($sql);
        $array = $result->fetchAssoc();
        if ($array) {
            return "The database $dbName is already running a db schema version at or greater than 2. Please check that
              modules/config.inc includes \$db_schema_version = XXX, where XXX is your current schema version.";
        }

        return true;
    }

    public function doUpgrade()
    {
        $sql = "ALTER TABLE ".Database::$prefix."users MODIFY COLUMN password varchar(255) NOT NULL;";
        $result = Database::run($sql);

        $sql = "ALTER TABLE ".Database::$prefix."users ADD COLUMN id INT(10) NOT NULL AUTO_INCREMENT UNIQUE FIRST;";
        $result = Database::run($sql);

        return true;
    }
}
