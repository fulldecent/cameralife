<?php
namespace CameraLife;

/**
 * Base class for all modules -- Retrieving of preferences
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */
abstract class CameraLifeModule
{
    public $name;

    /**@var mixed $preferences
     *
     * An array having arrays of:
     * <ul>
     *  <li> an named array with:</li>
     *  <li> 'name' => string</li>
     *  <li> 'desc' => string</li>
     *  <li> 'type' => 'directory', 'directoryrw' (must be read/writeable), 'string', 'number', 'yesno' ('0' or '1'), or an array of possible values (enum)</li>
     *  <li> 'default' => the default value</li>
     * </ul>
     */
    public $preferences;
    private $cameralife;

    public function __construct($cameralife = null)
    {
        $this->cameralife = $cameralife;
    }

    public function getPref($key)
    {
        $class = str_replace('CameraLife\\', '', get_class($this));
        if (isset($this->cameralife->userpreferences[$class][$key])) {
            return $this->cameralife->userpreferences[$class][$key];
        }

        foreach ($this->preferences as $pref) {
            if ($pref['name'] == $key) {
                return $pref['default'];
            }
        }

        $this->cameralife->error("Unable to retrieve preference: $class $key");
        return false;
    }

    /**
     * Pass VALUE=NULL to reset to default
     */
    function setPref($key, $value)
    {
        if ($value == null) {
            foreach ($this->preferences as $pref) {
                if ($pref['name'] == $key) {
                    $value = $pref['default'];
                }
            }
        }
        $this->cameralife->userpreferences[get_class($this)][$key] = $value;
        $this->cameralife->savePreferences();
    }
}
