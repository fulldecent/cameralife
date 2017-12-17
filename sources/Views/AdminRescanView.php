<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;

/**
 * Simple view for rendering a list of configurable preferences
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class AdminRescanView extends View
{
    public $scanResults;

    /**
     * thumbnailUrl
     *
     * @var    mixed
     * @access public
     */
    public $thumbnailUrl;

    public function render()
    {
        echo "<h2>Rescanning photos...</h2>\n";

        echo "<ul>";
        foreach ($this->scanResults as $result) {
            echo "<li>" . htmlspecialchars($result) . "</li>";
        }
        echo "</ul>";
        echo "<p><a class=\"btn btn-info\" href=\"$this->thumbnailUrl\"><i class=\"fa fa-folder-open\"></i> Update thumbnails</a></p>";
    }
}
