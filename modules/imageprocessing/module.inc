<?php
/**
 * Manipulates images
 * 
 * @extends CameraLifeModule
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */
abstract class ImageProcessingModule extends CameraLifeModule
{
    abstract public function createImage($filename);
}

/**
 * The editable image
 */
abstract class ImageProcessingImage
{
    /**
     * __construct function.
     * 
     * @access public
     * @abstract
     * @param mixed $filename
     * @return void
     */
    abstract public function __construct($filename);

    /**
     * Clean up.
     * 
     * @access public
     * @abstract
     * @return void
     */
    abstract public function destroy();

    /**
     * check function.
     * 
     * @access public
     * @return bool
     */
    abstract public function check();

    /**
     * Resizes this image and saves to this new file
     * 
     * @access public
     * @param string $filename
     * @param int $newSize Diagonal of the new image size
     * @param int $quality (default: 91)
     * @return width, height of the image
     */
    abstract public function resize($filename, $newSize, $quality = 91);
}