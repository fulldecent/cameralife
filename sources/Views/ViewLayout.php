<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;

/**
 * This is a view which encapsulates another view. Akin to the LAYOUT concept
 * in Jekyll.
 *
 * @author    William Entriken <cameralife@phor.net>
 * @copyright 2017 William Entriken
 * @access    public
 */
abstract class ViewLayout
{
    public $subView;

    abstract protected function renderTop();

    abstract protected function renderBottom();

    public function render()
    {
        $this->renderTop();
        $this->subView->render();
        $this->renderBottom();
    }
}
