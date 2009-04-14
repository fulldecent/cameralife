<?php
  //    Do stuff with photos
   /**Enables operations on images
   *
   *such as
   *<ul>
   *<li>Opening</li>
   *<li>Deleting</li>
   *<li>Confirming succesful uploading of image</li>
   *<li>Getting size of image</li>
   *<li>Resizing the image</li>
   *</ul>
   *@version 2.6.2
   *@author Will Entriken <cameralife@phor.net>
   *@copyright © 2001-2009 Will Entriken
   *@access public
   *@link http://fdcl.sourceforge.net
   */
/**Carries functions to create and process images
*/
  class ImageProcessingModule extends CameraLifeModule
  {
    function ImageProcessingModule () { }

    function CreateImage($filename) { }
  }
/**Carries functions that enable operations on images
*/
  class ImageProcessingImage
  {
    /* Open an image for processing */
    /**Open an image for processing 
    */
    function ImageProcessingImage ($filename) { }

    /* Clean up */
    /**Clean up
    */
    function Destroy() { }

    /* Make sure image loaded OK
      return TRUE on success
    */
    /**Confirmation of successful confirmation of image.
    *Returns TRUE on success
    *
    */
    function Check() { }
    
    /* Returns (width, height) of this img
    */
    /**Returns (width,height ) of the image
    function GetSize() { }

    /* Resizes this image and saves to this new file
      SIZE is the diagonal of the new image size
      returns the dimensions of the new image
    */
    /**Resizes this image and saves to this new file.
    *  SIZE is the diagonal of the new image 
    *
     * Returns the dimensions of the new image
    *
    */
    function Resize($filename, $newSize, $quality = 91) { }
  }


?>
