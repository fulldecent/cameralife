<?php
namespace CameraLife;

class SchemaUpdater1 extends SchemaUpdater
{
    public function __construct()
    {
        $this->scriptInfo = <<<HERE
There is no automatic upgrade from your current database schema (0) to version 1.
Please follow instructions in UPGRADE to complete this.
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
