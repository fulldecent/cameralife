<?php
namespace CameraLife;

/**
 * The base class for themes
 */
abstract class ThemeModule extends CameraLifeModule
{
    /**
     *  Displays all HTML to the user given the specified name and associated data
     *
     * @param string $name   the name of the page
     * @param mixed  $object any extra data
     */
    abstract public function showPage($name, $object = null);
}
