<?php
/**
 * View class.
 * Every user-facing page is represented by a view class
 *
 * @author Will Entriken <cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2001-2009 Will Entriken
 */
class View
{
  /**
   * GetIcon function.
   * 
   * @access public
   * @param string $size (default: 'large')
   * @return array with keys: href, name, image 
   */
  public function GetIcon($size='large') {}

  /**
   * GetPrevious function.
   * A URL or NULL for the page logically preceeding this one
   * 
   * @access public
   * @return void
   */
  public function GetPrevious() {}

  /**
   * GetNext function.
   * A URL or NULL for the page logically following this one
   * 
   * @access public
   * @return void
   */
  public function GetNext() {}
  
  /**
   * GetPrevious function.
   * A URL or NULL for the page logically following this one
   * 
   * @access public
   * @return void
   */
     
  /**
   * ShowPage function.
   * The the currently installed theme to display this view
   * 
   * @access public
   * @return void
   */
  public function ShowPage()
  {
    global $cameralife;

    $cameralife->Theme->ShowPage(strtolower(get_class($this)), $this);
  }
}
?>