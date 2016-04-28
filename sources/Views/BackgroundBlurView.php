<?php
namespace CameraLife\Views;

/**
 * Breadcrumbs view
 *
 * @author    William Entriken<cameralife@phor.net>
 * @access    public
 * @copyright 2014 William Entriken
 */
class BackgroundBlurView extends View
{
    /**
     * An image to use as a blurred background
     *
     * @var    String
     * @access public
     */
    public $imageURL;

    /**
     * Render the view to standard output
     *
     * @access public
     * @return void
     */
    public function render()
    {
	    echo '<div id="blurredBackground" style="z-index:-1;position:fixed;top:0;left:0;width:140%;height:140%;margin:-20%;background:url(';
		echo htmlspecialchars($this->imageURL);
		echo ');background-size:cover;-webkit-filter: blur(25px) grayscale(25%) opacity(60%); filter: blur(25px) grayscale(25%) opacity(60%)"></div>';
    }
}
