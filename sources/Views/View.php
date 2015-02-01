<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;

/**
 * Base class for all View objects, each View writes a complete or partial document
 * (usually in HTML) to standard output. All information the view needs for
 * rendering will be contained in concrete subclass properties. Views must not
 * call header() or other side-channel output.
 *
 * @author    William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access    public
 */
abstract class View
{
    /**
     * Render the view to standard output
     *
     * @access public
     * @return void
     */
    abstract public function render();
}
