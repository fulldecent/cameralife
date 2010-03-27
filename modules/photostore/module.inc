<?php
/**
 * A photostore can store and retrieve photos to a filesystem. Also,
 * the photostore knows about thumbnails/modifications of a photo.
 *
 * (Optional) The photostore can also provide a URL for resources so the
 * website visitor can access resources directly.
 *
 * A photo "added" to a photostore will have its CHMOD set correctly.
 * 
 * Generally, you will update the photo object and then call the functions.
 * @version 2.6.2
 * @author Will Entriken<cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2001-2009 Will Entriken
 */
class PhotoStore extends CameraLifeModule
{
  function PhotoStore() {}

  /**
   * Gets a URL for the client to access the selected resource. Or return FALSE.
   * If FALSE, the caller must construct a URL to media.php
   * That page will GetFile() the photo and display it to the user.
   * 
   * (Implementation note: make sure non-public photos do not have 
   * publicly accessible urls)
   *
   * @param $photo - a target photo obeject
   * @param $type - one of ('photo', 'scaled', 'thumbnail', a number)
   * @return a URL or FALSE
   */
  function GetURL($photo, $type='photo') 
  {
    return FALSE;
  }

  /**
  *  GetFile
  *
  *  Gets a filename for the requested resource. It is downloaded if necessary.
  *
    *  @param $photo - a target photo object
    *  @param $type - one of ('photo', 'original', 'modified', 'scaled', 'thumbnail', a number)
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
    *  Puts a new photo into the photostore. Photo should be put with appropriate permissions.
    *  The file $filename will not be modified or deleted.
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
    *  @param array $thumbs - an array of (imagesize, filename) to insert
    *
    *  @return none
    */
    function PutThumbnails($photo, $thumbs) {}

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
    *  for the filename will revert the file to the original. The passed
    *  file is destroyed.
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
