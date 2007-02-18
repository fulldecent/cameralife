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


  class SecurityModule extends CameraLifeModule
  {
    function SecurityModule () { }
  
    /* Return TRUE or a string that is an error message */
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
    function GetAddr () { }

    /* Determines if the user is authoried to performed the named action,
    ** if REQUIRED is set, nonauthorization is a fatal error.
    ** This function must at least support the actions listed above
    */
    function Authorize ($action, $required=false) { }
  }

?>
