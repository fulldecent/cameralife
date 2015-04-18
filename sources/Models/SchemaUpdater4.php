<?php
namespace CameraLife\Models;

/**
 * Updates the database schema
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2015 William Entriken
 * @access public
 */
class SchemaUpdater4 extends SchemaUpdater
{
    public $scriptInfo = <<<HERE
This will make the following changes:
<ul>
  <li>set photos.path to have the format /path instead of path/</li>
  <li>Create an index on photos.path | photos.filename</li>
</ul>
HERE;

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
        return true;
    }

    public function doUpgrade()
    {
        $sql = "ALTER TABLE ".Database::$prefix."photos ADD UNIQUE INDEX (filename, path);";
        $result = Database::run($sql);

        $sql = "UPDATE ".Database::$prefix."photos SET path = mid(path,2) WHERE path LIKE '/%';";
        $result = Database::run($sql);

        $sql = "UPDATE ".Database::$prefix."photos SET path = mid(path,1,length(path)-1) WHERE path LIKE '%/';";
        $result = Database::run($sql);

        $sql = "UPDATE ".Database::$prefix."photos SET path = concat('/',path);";
        $result = Database::run($sql);

        return true;
    }
}
