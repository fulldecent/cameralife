<?php
namespace CameraLife\Models;

/**
 * A string key-value store for preferences that is specific to each class and plugin
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class Preferences
{
    private static $cachedPreferences;

    /**
     * Returns the value string or null if none exists
     *
     * @access public
     * @param  string $class
     * @param  string $key
     * @return mixed string | null
     */
    public static function valueForModuleWithKey($module, $key)
    {
        if (empty(self::$cachedPreferences)) {
            $database = new Database();
            $query = $database->select('preferences', '*');
            while ($row = $query->fetchAssoc()) {
                self::$cachedPreferences[$row['prefmodule']][$row['prefkey']] = $row['prefvalue'];
            }
        }
        if (isset(self::$cachedPreferences[$module][$key])) {
            return self::$cachedPreferences[$module][$key];
        } else {
            return null;
        }
    }

    public static function setValueForModuleWithKey($value, $module, $key)
    {
        self::$cachedPreferences[$module][$key] = $value;
        $values = ['prefvalue' => $value];
        $condition = "prefmodule='$module' AND prefkey='$key'";
        $database = new Database();
        $query = $database->select('preferences', '1', $condition);
        if ($query->fetchAssoc()) {
            $database->update('preferences', $values, $condition);
        } else {
            $values['prefdefault'] = $value;
            $values['prefmodule'] = $module;
            $values['prefkey'] = $key;
            $database->insert('preferences', $values);
        }
    }
    
    public static function setFactoryDefaults()
    {
        self::setValueForModuleWithKey(date('Y-m-d H:i:s'), 'CameraLife', 'sitedate');
        self::setValueForModuleWithKey('My Photos', 'CameraLife', 'sitename');
        self::setValueForModuleWithKey('yes', 'CameraLife', 'autorotate');
        self::setValueForModuleWithKey('180', 'CameraLife', 'thumbsize');
        self::setValueForModuleWithKey('800', 'CameraLife', 'scaledsize');
        self::setValueForModuleWithKey('', 'CameraLife', 'optionsizes');
        self::setValueForModuleWithKey('photos', 'LocalFileStore', 'photo_dir');
        self::setValueForModuleWithKey('caches', 'LocalFileStore', 'cache_dir');
    }
}
