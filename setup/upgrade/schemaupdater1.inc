<?php
namespace CameraLife;
require_once './schemaupdater.inc';

class SchemaUpdater1 extends SchemaUpdater
{
    public function __construct()
    {
        $this->scriptInfo = <<<HERE
There is no automatic upgrade from your current database schema (0) to version 1.
Please follow instructions in UPGRADE to complete this. This file existed between
2005 and 2014. For the archive, see:
https://raw.githubusercontent.com/fulldecent/cameralife/77748b47b7ebfdd15a0179d7d374ee97039ad0e7/UPGRADE
HERE;
    }

    public function canUpgrade($dbHost, $dbName, $dbUser, $dbPass, $dbPrefix)
    {
        return false;
    }

    public function doUpgrade($dbHost, $dbName, $dbUser, $dbPass, $dbPrefix)
    {
        return false;
    }
}
