<?php
namespace CameraLife\Models;

/**
 * Updates the database schema
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2015 William Entriken
 * @access public
 */
class SchemaUpdater5 extends SchemaUpdater
{
    public $scriptInfo = <<<HERE
This will make the following changes:
<ul>
  <li>Update photos.modified to be a VARCHAR field
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
        $sql = "ALTER TABLE ".Database::$prefix."photos CHANGE modified modified VARCHAR(255);";
        $result = Database::run($sql);

        $sql = "UPDATE ".Database::$prefix."photos SET modified = NULL where modified = 0;";
        $result = Database::run($sql);

        return true;
    }
}
