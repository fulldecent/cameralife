<?php
  # Every user-facing page is represented by a view class
  # 
  
class View
{
  /**
   * Some standard information for a page
   * @param string $size whether the images is 'large' or 'small'
   * @return array with keys: href, name, image
   *
   * return may also include: context (flavor text), rel and rev (from HTML a element attributes)
   *   and width and height (for the image)
   * image will be a URL or a named icon (see IconSet modules)
   */
  function GetIcon($size='large') {}
}

?>
