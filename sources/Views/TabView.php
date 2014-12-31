<?php
namespace CameraLife\Views;

/**
 * Breadcrumbs view
 *
 * @author William Entriken<cameralife@phor.net>
 * @access public
 * @copyright 2014 William Entriken
 */
class TabView extends View
{
    /**
     * The objects to base this view on
     *
     * @var CameraLife\Controllers\Controller[]
     * @access public
     */
    public $openGraphObjects;

    /**
     * The number of the currently selected item
     *
     * @var int
     * @access public
     */
    public $selected;

    /**
     * Render the view to standard output
     *
     * @access public
     * @return void
     */
    public function render()
    {
        echo '<ul class="nav nav-tabs">' . PHP_EOL;
        foreach ($this->openGraphObjects as $i => $openGraphObject) {
            if ($i == $this->selected) {
                echo "<li class=\"active\">";
            } else {
                echo "<li>";
            }
            echo "<a href=\"".htmlspecialchars($openGraphObject->url)."\">";
            echo htmlspecialchars($openGraphObject->title);
            echo "</a>";
            echo "</li>";
        }
        echo "</ul>";
    }
}
