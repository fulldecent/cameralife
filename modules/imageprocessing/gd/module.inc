<?php
 //GD image functions
 /**Processing GD images
 *@version 2.6.3b4
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  *@link http://fdcl.sourceforge.net
  */
  /** Carries functions to create and process images
  */

  class GDImageProcessing extends ImageProcessingModule
  {
    function CreateImage ($filename)
    {
      return new GDImageProcessingImage($filename);
    }
  }
  /**Carries functions that enable image editing 
  */

  class GDImageProcessingImage extends ImageProcessingImage
  {
    var $original_image;
    var $width;
    var $height;
    var $size;
    var $extension;

    var $scaled_images;

    function GDImageProcessingImage($filename)
    {
      global $cameralife;
      if (!file_exists($filename))
        $cameralife->Error('Trying to process non-existant image: '.$filename, __FILE__, __LINE__);
      $path_parts = pathinfo($filename);
      $this->extension = strtolower($path_parts['extension']);

      if ($this->extension=='jpeg' || $this->extension=='jpg' || $this->extension=='' ) # going out on a limb here...
        $this->original_image = imagecreatefromjpeg($filename);
      elseif ($this->extension=='png')
        $this->original_image = imagecreatefrompng($filename);
      elseif ($this->extension=='gif')
        $this->original_image = imagecreatefromgif($filename);
      else
        $cameralife->Error('Trying to process image of unknown file format: '.$filename, __FILE__, __LINE__);
      $this->width = imagesx($this->original_image);
      $this->height = imagesy($this->original_image);
      $this->size = sqrt($this->width*$this->width + $this->height*$this->height);
    }

    function Destroy()
    {
      imagedestroy($this->original_image);
      if (count($this->scaled_image) > 0)
        foreach ($this->scaled_image as $image)
          imagedestroy($image);
    }

    function Check()
    {
      return ($this->original_image == TRUE);
    }

    function GetSize()
    {
      return array($this->width, $this->height); 
    }

    /**Resizes this image and saves to this new file
     * SIZE is the diagonal of the new image size
     * 
     *  If you have already resized the image to something larger than you want
     * you can rescale it. This is much faster than scaling the original, larger, image again. 
     *@return float array - dimensions new width and height
    */
    function Resize($filename, $newSize, $quality = 91) 
    {
      /* If we have already resized this image to something larger than we want
      ** now, we can scale from it. This is much faster than
      ** scaling the original, larger, image again. 
      */
      global $cameralife;

      $base_image = $this->original_image;
      $base_size = $this->size;

      if (count($this->scaled_images))
      foreach ($this->scaled_images as $size => $image)
      {
        if ($size < $base_size && $size > $newSize)
        {
          $base_image = $image;
          $base_size = $size;
        }
      }
      $base_width = $this->width * $base_size / $this->size;
      $base_height = $this->height * $base_size / $this->size;
      
      $new_width = $this->width * $newSize / $this->size;
      $new_height = $this->height * $newSize / $this->size;
      $new_image = imagecreatetruecolor($new_width, $new_height)
        or die("Can't make new image");

      imagecopyresampled($new_image, $base_image,
                         0, 0, 0, 0,
                         $new_width, $new_height,
                         $base_width, $base_height);
 
      if ($this->extension=='jpeg' || $this->extension=='jpg' || $this->extension=='') # wild guess
      {
        @imagejpeg($new_image, $filename, $quality)
          or $cameralife->Error("Could not write the file $filename is the directory writable?", __FILE__, __LINE__);
      }
      elseif ($this->extension=='png')
      {
        @imagepng($new_image, $filename, 9-$quality/11)
          or $cameralife->Error("Could not write the file $filename is the directory writable?", __FILE__, __LINE__);
      }
      elseif ($this->extension=='gif')
      {
        @imagegif($new_image, $filename, 9-$quality/11)
          or $cameralife->Error("Could not write the file $filename is the directory writable?", __FILE__, __LINE__);
      }

      $this->scaled_image[$newSize] = $new_image;

      return array($new_width, $new_height);
    }

    /**Rotate the image a 90, 180 or 270 degrees
    */
    function Rotate($degrees) 
    {
      @ini_set('max_execution_time',100);

      if (function_exists('imagerotate'))
        $rotated = imagerotate($this->original_image, -$degrees, 0);
      else
        $rotated = ImageRotateRightAngle($this->original_image, $degrees);
      if ($degrees == 90 || $degrees == 270)
      {
        $oldwidth = $this->width;
        $this->width = $this->height;
        $this->height = $oldwidth;
      }
      $this->Destroy();
      $this->original_image = $rotated;
    }

    /* Save the image at original resolution
    */
    /**Saves the image at orginal resolution
    */
    function Save($filename, $quality = 91) 
    {
      if ($this->extension=='jpeg' || $this->extension=='jpg' || $this->extension=='') # on a limb...
      {
        @imagejpeg($this->original_image, $filename, $quality)
          or $cameralife->Error("Could not write the file $filename is the directory writable?", __FILE__, __LINE__);
      }
      elseif ($this->extension=='png')
      {
        @imagepng($this->original_image, $filename, 9-$quality/11)
          or $cameralife->Error("Could not write the file $filename is the directory writable?", __FILE__, __LINE__);
      }

      return array($new_width, $new_height);
    }
  }
  /**@var $srcX dimension of source image
  *@var $srcY dimension of source image
  */

  function ImageRotateRightAngle( $imgSrc, $angle )
  {
    // dimenstion of source image
    $srcX = imagesx( $imgSrc );
    $srcY = imagesy( $imgSrc );
    if ($angle==90 || $angle==270)
      $imgDest = imagecreatetruecolor( $srcY, $srcX );
    else
      $imgDest = imagecreatetruecolor( $srcX, $srcY );
      
    if ($angle == 90)
    {
      for( $x=0; $x<$srcX; $x++ )
          for( $y=0; $y<$srcY; $y++ )
              imagecopy($imgDest, $imgSrc, $srcY-$y-1, $x, $x, $y, 1, 1);
    } elseif ($angle == 270) {
      for( $x=0; $x<$srcX; $x++ )
          for( $y=0; $y<$srcY; $y++ )
              imagecopy($imgDest, $imgSrc, $y, $srcX-$x-1, $x, $y, 1, 1);
    } elseif ($angle == 180) {
      for( $x=0; $x<$srcX; $x++ )
          for( $y=0; $y<$srcY; $y++ )
              imagecopy($imgDest, $imgSrc, $srcX-$x-1, $srcy-$y-1, $x, $y, 1, 1);
    }
  
    return( $imgDest );
  } 


