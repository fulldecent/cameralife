<?php
  /*
  *  A photostore is a backend for storing photos. Its responsibilities are:
  *    - putting and getting photo files
  *    - taking photos and making scaled/thumbnailed versions
  *    - making thes files accessible by a URL
  *
  *  Generally, you will update the photo object and then call these functions.
  *  The photostore will look at the photo object to find the postcondition
  *  and then make it happen.
  *
  *  Implementation note: for all files passed in, you are responsible for 
  *  chmodding as necessary.
  */

  class PhotoStore extends CameraLifeModule
  {
    function PhotoStore() {}

    /**
    *  GetURL
    *
    *  Gets a URL for the client to access the selected resource. Or return FALSE.
    *  If FALSE, the caller must construct a URL to media.php
    *  That page will GetFile() the photo and display it to the user.
    * 
    *  (Implementation note: make sure non-public photos do not have 
    *  publicly accessible urls)
    *
    *  @param $photo - a target photo obeject
    *  @param $type - one of ('photo', 'scaled', 'thumbnail')
    *
    *  @return a URL or FALSE
    */
    function GetURL($photo, $type='photo') {}

    /**
    *  GetFile
    *
    *  Gets a filename for the requested resource. It is downloaded if necessary.
    *
    *  @param $photo - a target photo object
    *  @param $type - one of ('photo', 'original', 'modified', 'scaled', 'thumbnail')
    *    'photo' refers to the active one of (original or modified)
    *
    *  @return an array with these elements:
    *     filename - string - the local file you requested
    *     temporary - boolean
    *     mtime - unix time that this file was modified
    *
    *  If temporary is set, the caller is responsible for deleting this file when done.
    */
    function GetFile($photo, $type='photo') {}

    /**
    *  PutFile
    *
    *  Puts a new photo into the photostore. Photo should be put with appropriate permissions
    *
    *  @param $photo - a target photo object
    *  @param $filename - the local filesystem file that we are putting
    *
    *  @return none
    */
    function PutFile($photo, $filename) {}

    /**
    *  PutThumbnails
    *
    *  Puts a a scaled photo and thumbnail into the photostore. Photo should be put with appropriate permissions
    *
    *  @param $photo - a target photo object
    *  @param $scaled - the file to insert
    *  @param $thumbnail - the file to insert
    *
    *  @return none
    */
    function PutThumbnails($photo, $scaled, $thumbnail) {}

    /**
    *  SetPermissions
    *
    *  Make the photo public or private on the photostore, according to $photo->Get('status')
    *
    *  @param $photo - a target photo object
    *
    *  @return none
    */
    function SetPermissions($photo) {}

    /**
    *  CheckThumbnails
    *
    *  Ensures that thumbnails exist
    *
    *  @param $photo - a target photo object
    *
    *  @return true if work was done
    */
    function CheckThumbnails($photo) {}

    /**
    *  ModifyFile
    *
    *  Puts a modified version of a photo into the photostore. Passing NULL
    *  for the filename will revert the file to the original.
    *
    *  @param $photo - a target photo object
    *  @param $filename - the local filesystem file that we are putting
    *
    *  @return none
    */
    function ModifyFile($photo, $filename) {}

    /**
    *  EraseFile
    *
    *  Erase a photo and associated scaled photos from the system
    *
    *  @param $photo - a target photo object
    *
    *  @return none
    */
    function EraseFile($photo) {}

    /**
    *  ListFiles
    *
    *  Returns a list of all files in the photostore
    *
    *  @param $path - relative path to hunt down
    *  @param $recursive - whether to list recursively
    *
    *  @return an array of files, in the form 'path'=>basename(path)
    */
    function ListFiles($path='', $recursive=TRUE) {}

  }
?>
