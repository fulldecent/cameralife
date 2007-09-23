<?php
  /*
    Do stuff with photos
  */

  class ImageProcessingModule extends CameraLifeModule
  {
    function ImageProcessingModule () { }

    function CreateImage($filename) { }
  }

  class ImageProcessingImage
  {
    /* Open an image for processing */
    function ImageProcessingImage ($filename) { }

    /* Clean up */
    function Destroy() { }

    /* Make sure image loaded OK
      return TRUE on success
    */
    function Check() { }
    
    /* Returns (width, height) of this img
    */
    function GetSize() { }

    /* Resizes this image and saves to this new file
      SIZE is the diagonal of the new image size
      returns the dimensions of the new image
    */
    function Resize($filename, $newSize, $quality = 91) { }
  }


?>
