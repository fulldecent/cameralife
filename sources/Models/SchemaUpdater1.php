<?php
namespace CameraLife\Models;

/**
 * Updates the database schema
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2015 William Entriken
 * @access public
 */
class SchemaUpdater1 extends SchemaUpdater
{
    public $scriptInfo = <<<HERE
There is no automatic upgrade from your current database schema (0) to version 1.
Please follow instructions in UPGRADE to complete this. This file existed between
2005 and 2014. For the archive, see:
https://raw.githubusercontent.com/fulldecent/cameralife/77748b47b7ebfdd15a0179d7d374ee97039ad0e7/UPGRADE
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
        return false;
    }

    public function doUpgrade()
    {
        return false;
    }
}
