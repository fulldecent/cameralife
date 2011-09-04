<?php
  # Every user-facing page is represented by a view class
  #
  /** Every page which the user can view is backed by a view class
  *@link http://fdcl.sourceforge.net/
  *@version 
  *@author Will Entriken <cameralife@phor.net>
  *@access public
  *@copyright Copyright (c) 2001-2009 Will Entriken
  */
  /**
  *For defining standard information for a page
  *
  *such as:
  *<ul>
  *<li>name of image </li>
  *<li>image size</li>
  *<li>date uploaded</li>
  *<li>icon</li>
  *</ul>
  */
class View
{
  /**
   * Some standard information for a page
   * @param string $size whether the images is 'large' or 'small'
   * @return array with keys: href, name, image
   *
   * return may also include: context (flavor text), rel and rev (from HTML a element attributes)
   *   and width and height (for the image), date (unix time)
   * image will be a URL
   *  these named icons will match ^[a-z-]+$
   *
   * href is HTML encoded, so you can <a href="$icon[href]">$icon[name]</a>
   */

   /**
   *Function GetIcon has argument $size indicating whether the image size is 'large' or "small'
   *
   *It returns
   *<ul>
   *<li>an array with keys href,name</li>
  	*<li>context(flavor text)</li>
  	*<li>rel and rev (from HTML a element attributes)</li>
  	*<li>width and height for the image</li>
  	*<li>date in unix time</li>
  	*</ul>
  	*<b>Note</b>  href is HTML encoded, so you code it as <a> href="$icon[href]">$icon[name]</a>
  	*The image will be linked to its specific URL
  	*The named icons should only consists of characters [a-z] or $ symbol
  	*
  	*@param string $size whether the images is 'large' or 'small'
   *@return array with keys: href, name, image
  	*/

  function GetIcon($size='large') {}

  function ShowPage()
  {
    global $cameralife;

    $cameralife->Theme->ShowPage(strtolower(get_class($this)), $this);
  }

}

?>
