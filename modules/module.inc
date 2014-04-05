<?php
/**
 * Base class for all modules -- Retrieving of preferences
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */
abstract class CameraLifeModule
{
    var $name;

    public function __construct()
    {
    }

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

    public function getPref($key)
    {
        global $cameralife;

        if (isset($cameralife->userpreferences[get_class($this)][$key])) {
            return $cameralife->userpreferences[get_class($this)][$key];
        }

        foreach ($this->preferences as $pref) {
            if ($pref['name'] == $key) {
                return $pref['default'];
            }
        }

        $cameralife->error('Unable to retreive preference: ' . get_class($this) . ' ' . $key);
        return FALSE;
    }

    /**
     * Pass VALUE=NULL to reset to default
     */
    function setPref($key, $value)
    {
        global $cameralife;

        if ($value == null) {
            foreach ($this->preferences as $pref) {
                if ($pref['name'] == $key) {
                    $value = $pref['default'];
                }
            }
        }

        $cameralife->userpreferences[get_class($this)][$key] = $value;
        $cameralife->savePreferences();
    }
}
