<?php
require_once('./schemaupdater.inc');

class SchemaUpdater1 extends SchemaUpdater
{
    public function __construct()
    {
        $this->scriptInfo = <<<HERE
There is no automatic upgrade from your current database schema (0) to version 1.
Please follow instructions in UPGRADE to complete this.
HERE;
    }

    public function canUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix)
    {
        return false;
    }

    public function doUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix)
    {
        return false;
    }
}
