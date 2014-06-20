<?php
namespace CameraLife;

abstract class SchemaUpdater
{
    public $scriptInfo;

    /**
     * Is this updater able to be executed on the current system?
     *
     * @access public
     * @abstract
     * @param mixed $dbHost
     * @param mixed $dbName
     * @param mixed $dbUser
     * @param mixed $dbPass
     * @param mixed $dbPrefix
     * @return mixed true for succes, string for failure
     */
    abstract public function canUpgrade($dbHost, $dbName, $dbUser, $dbPass, $dbPrefix);

    /**
     * Execute the upgrade
     *
     * @access public
     * @abstract
     * @param mixed $dbHost
     * @param mixed $dbName
     * @param mixed $dbUser
     * @param mixed $dbPass
     * @param mixed $dbPrefix
     * @return mixed true for succes, string for failure
     */
    abstract public function doUpgrade($dbHost, $dbName, $dbUser, $dbPass, $dbPrefix);
}
