<?php
  /*
    NOTE: This "API" is subject to change. Specifically, the part about URL's
    I don't know the best way to implement that!

  * The following named security actions must be supported by Authorize():
  *  photo_rename
  *  photo_delete
  *  photo_modify
  *  admin_albums
  *  photo_upload
  *  admin_file
  *  admin_theme 
  *  admin_customize
  */
  /**This "API" is subject to change; specifically the URL part will be altered.
  *
  *The following named security actions must be supported by Authorize():
  *<ul>
  *  <li>photo_rename</li>
  *  <li>photo_delete</li>
  * <li>photo_modify</li>
  * <li>admin_albums</li>
  * <li>photo_upload</li>
  * <li>admin_file</li>
  * <li>admin_theme </li>
  * <li>admin_customize</li>
  *</ul>
  *@link http://fdcl.sourceforge.net/
  *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@access public
  *@copyright Copyright (c) 2001-2009 Will Entriken
  */
  /**This module implements security features
  */


  class SecurityModule extends CameraLifeModule
  {
    function SecurityModule () { }
  
    /*Returns TRUE or a string that is an error message */
    /**@return TRUE|error message string
    */
    function Login ($username, $password) { }

    /* Logs the user out. Returns true  or and exit url if you really want to */
    function Logout () { }

    /* Provides a URL, relative to $cameralife->base_url that the user can
    ** go to, to administer users and permissions. Or returns FALSE, if the 
    ** user is not allowed to administer.
    */
    function AdministerURL () { }

    /* Returns the current user's name or the empty string if not logged in */
    function GetName () { }

/* This is subject to change... is this necessary??? LMK */
    /* Returns the current user's IP address */
    /**Returns the current user's IP address
    *This is subject to change.Do you find this feature is useful?Please email us your comments.
    */
    function GetAddr () { }

    /* Determines if the user is authoried to performed the named action,
    ** if REQUIRED is set, nonauthorization is a fatal error.
    ** This function must at least support the actions listed above
    */
  /**Determines if the user is authoried to performed the named action,
    * if REQUIRED is set, nonauthorization is a fatal error.
    * This function must at least support the follwing actions 
    <ul>
      *  <li>photo_rename</li>
      *  <li>photo_delete</li>
      * <li>photo_modify</li>
      * <li>admin_albums</li>
      * <li>photo_upload</li>
      * <li>admin_file</li>
      * <li>admin_theme </li>
      * <li>admin_customize</li>
  *</ul>
    */  
    function Authorize ($action, $required=false) { }
  }

?>
