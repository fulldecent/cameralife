<?php
abstract class SchemaUpdater
{
    public $scriptInfo;

    /**
     * Is this updater able to be executed on the current system?
     *
     * @access public
     * @param $db_host
     * @param $db_name
     * @param $db_user
     * @param $db_pass
     * @param $db_prefix
     * @return mixed true for succes, string for failure
     */
    abstract public function canUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix);

    /**
     * Execute the upgrade
     *
     * @access public
     * @param $db_host
     * @param $db_name
     * @param $db_user
     * @param $db_pass
     * @param $db_prefix
     * @return mixed true for succes, string for failure
     */
    abstract public function doUpgrade($db_host, $db_name, $db_user, $db_pass, $db_prefix);
}