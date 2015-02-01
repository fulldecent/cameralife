<?php
namespace CameraLife\Views;

/**
 * Breadcrumbs view
 *
 * @author    William Entriken<cameralife@phor.net>
 * @access    public
 * @copyright 2014 William Entriken
 */
class BreadcrumbView extends View
{
    /**
     * The objects to base this view on
     *
     * @var    CameraLife\Controllers\Controller[]
     * @access public
     */
    public $openGraphObjects;

    /**
     * Render the view to standard output
     *
     * @access public
     * @return void
     */
    public function render()
    {
        echo '<ul class="breadcrumb">';
        foreach ($this->openGraphObjects as $i => $openGraphObject) {
            $icon = '<i class="fa fa-' . $openGraphObject->icon . '"></i> ';
            $htmlTitle = htmlspecialchars($openGraphObject->title);
            $htmlHref = htmlspecialchars($openGraphObject->url);
            if ($i == count($this->openGraphObjects) - 1) {
                echo "<li class=\"active\">$icon$htmlTitle</li>";
            } else {
                echo "<li><a href=\"$htmlHref\">$icon$htmlTitle</a></li>";
            }
        }
        echo '</ul>';
    }
}
