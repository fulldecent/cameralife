<?php

/**
 * View class.
 * Every user-facing page is represented by a view class
 *
 * @author William Entriken <cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2001-2009 William Entriken
 */
class View
{
    public function __construct()
    {
    }

    /**
     * getOpenGraph function.
     *
     * @access public
     * @return array
     *   og:title REQUIRED
     *   og:type "website" REQUIRED
     *   og:url REQUIRED
     *   og:description OPTIONAL
     *   og:determiner "a" | "an" | "the" | "" OPTIONAL (modifies title)
     *   og:site_name OPTIONAL
     *   og:image URL to an image thumbnail REQUIRED
     *   og:image:secure_url URL to an image thumbnail OPTIONAL
     *   og:image:type mimetype of thumbnail OPTIONAL
     *   og:image:width size of thumbnail OPTIONAL
     *   og:image:height size of thumbnail OPTIONAL
     */
    public function getOpenGraph()
    {

    }

    /**
     * GetPrevious function.
     * A URL or NULL for the View logically preceeding this one
     *
     * @access public
     * @return View
     */
    public function getPrevious()
    {
    }

    /**
     * getNext function.
     * A URL or NULL for the View logically following this one
     *
     * @access public
     * @return View
     */
    public function getNext()
    {
    }

    /**
     * showPage function.
     * Render this View using the currently installed theme
     *
     * @access public
     * @return void
     */
    public function showPage()
    {
        global $cameralife;
        $cameralife->getFeature('theme');
        $cameralife->theme->ShowPage(strtolower(get_class($this)), $this);
    }
}
