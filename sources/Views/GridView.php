<?php
namespace CameraLife\Views;

/**
 * Grid view
 *
 * @author    William Entriken<cameralife@phor.net>
 * @access    public
 * @copyright 2014 William Entriken
 */
class GridView extends View
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
        echo '<div>' . PHP_EOL;
        foreach ($this->openGraphObjects as $view) {
            $openGraphObject = $view;

            echo '<div class="l1" style="margin-bottom:1em">' . PHP_EOL;
            echo '<a class="l2" href="' . htmlspecialchars($openGraphObject->url) . '">';
            if (isset($openGraphObject->imageWidth) && isset($openGraphObject->imageHeight)) {
                $imageattrs = array(
                    'alt' => $openGraphObject->title,
                    'width' => $openGraphObject->imageWidth,
                    'height' => $openGraphObject->imageHeight,
                    'class' => 'l3'
                );
            } else {
                $imageattrs = array('alt' => $openGraphObject->title, 'class' => 'l3');
            }

            echo '<img src="' . $openGraphObject->image . '"';
            foreach ($imageattrs as $attr => $val) {
                if ($val) {
                    echo " $attr=\"$val\"";
                }
            }
            echo '">';

            if (isset($openGraphObject->imageWidth) && isset($openGraphObject->imageHeight)) {
                echo '<div class="l4" style="width:' . $openGraphObject->imageWidth . 'px">';
            } else {
                echo '<div class="l4">';
            }
            if ($openGraphObject->title != 'unnamed') {
                echo htmlentities($openGraphObject->title);
            }
            echo '</div>';
            echo '</a>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
        }
        echo '</div>' . PHP_EOL;
    }
}
