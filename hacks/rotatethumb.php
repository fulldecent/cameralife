<?php
#returns a rotated thumbnail

  $features=array('database','security','imageprocessing');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);
  chdir ($cameralife->base_dir);

  $cameralife->Security->authorize('admin_file', 1);

  $image = $_GET['id'];
  $degrees = $_GET['rotate'];
  $result = $cameralife->Database->Select('photos','*','id='.$image);
  $photo = $result->FetchAssoc()
    or die('You have followed a stale link or have found a bug in this site :-)');

  $file = $cameralife->preferences['core']['cache_dir'].'/'.$photo['id'].'_150.jpg';
  if (!file_exists($file))
  {
    $size = 150;
    $origphoto = $cameralife->ImageProcessing->CreateImage($cameralife->preferences['core']['photo_dir'] .'/'. $photo['path'] . $photo['filename']);
    $origphoto->Check()
      or die("Could not read photo, is it corrupted?");

    $origphoto->Resize($file, $size);
    $origphoto->Destroy();
  }

  $original_image = imagecreatefromjpeg($file);

  function HACKImageRotateRightAngle( $imgSrc, $angle )
  {
    // dimenstion of source image
    $srcX = imagesx( $imgSrc );
    $srcY = imagesy( $imgSrc );
    $imgDest = imagecreatetruecolor( $srcY, $srcX );
  
    if ($angle == 90)
    {
      for( $x=0; $x<$srcX; $x++ )
          for( $y=0; $y<$srcY; $y++ )
              imagecopy($imgDest, $imgSrc, $srcY-$y-1, $x, $x, $y, 1, 1);
    }else{
      for( $x=0; $x<$srcX; $x++ )
          for( $y=0; $y<$srcY; $y++ )
              imagecopy($imgDest, $imgSrc, $y, $srcX-$x-1, $x, $y, 1, 1);
    }
  
    return( $imgDest );
  } 

  if (function_exists('imagerotate'))
    $rotated = imagerotate($original_image, -$degrees, 0);
  else
  {
    if ($degrees==0) $rotated = $original_image;
    elseif ($degrees==90 || $degrees==270)
      $rotated = ImageRotateRightAngle($original_image, $degrees);
    else 
    {
      $rotated = ImageRotateRightAngle($original_image, $degrees);
      $rotated = ImageRotateRightAngle($rotated, $degrees);
    }
  }

  header('Content-type: image/jpeg');
  header('Content-Disposition: inline; filename='.htmlentities($photo['description']).'.jpg');
# header('Cache-Control: '.($photo['status'] > 0) ? 'private' : 'public');
#  header('Date: '.filemtime($file));

  imagejpeg($rotated);
?>