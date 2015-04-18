<?php
namespace CameraLife\Models;

/**
 * Updates the database schema
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2015 William Entriken
 * @access public
 */
abstract class SchemaUpdater
{
    public $scriptInfo;

    /**
     * Is this updater able to be executed on the current system?
     *
     * @access   public
     * @abstract
     * @return   mixed true for succes, string for failure
     */
    abstract public function canUpgrade();

    /**
     * Execute the upgrade
     *
     * @access   public
     * @abstract
     * @return   mixed true for succes, string for failure
     */
    abstract public function doUpgrade();
}
