<?php
  /*
  *  This module stores files on the local filesystem, and delivers them
  *  via another script. The user doesn't see the filename, so they can't
  *  (easily) guess what a private filename would be. Also, access to 
  *  private photos is authenticated.
  *
  */

  class LocalPhotoStore extends PhotoStore
  {
    function LocalPhotoStore() 
    {
      $this->name = 'Local';

      $this->preferences[] = array('name'=>'photo_dir', 'desc'=>'Main photo directory', 'type'=>'directory', 'default'=>'images/photos');
      $this->preferences[] = array('name'=>'cache_dir', 'desc'=>'Camera Life data directory', 'type'=>'directory', 'default'=>'images/cache');
      $this->preferences[] = array('name'=>'deleted_dir', 'desc'=>'Deleted photos (...where they go when you "erase" them)', 'type'=>'directory', 'default'=>'images/deleted');
    } 

    /**
    *  GetURL
    *
    *  Gets a URL for the client to access the selected resource.
    *  (Implementation note: make sure non-public photos do not have 
    *  publicly accessible urls)
    *
    *  @param $photo - a target photo obeject
    *  @param $type - one of ('photo', 'scaled', 'thumbnail')
    *
    *  @return a URL
    */
    function GetURL($photo, $type='photo') 
    {
      return FALSE; // this request will be handled through media.php
      // see note in photstore/module.php
    }

    /**
    *  GetFile
    *
    *  Gets a filename for the requested resource. It is downloaded if necessary.
    *
    *  @param $photo - a target photo object
    *  @param $type - one of ('photo', 'original', 'modified', 'scaled', 'thumbnail')
    *    'photo' refers to the modified (if exists) otherwise original
    *
    *  @return an array with two elements:
    *     filename - string - the local file you requested
    *     temporary - boolean
    *     mtime ...
    *
    *  If temporary is set, the caller is responsible for deleting this file when done.
    */
    function GetFile($photo, $type='photo') 
    {
      global $cameralife;

      $extension = $photo->extension;

      if ($type == 'photo')
      {
        if ($photo->Get('modified'))
          $file = $cameralife->base_dir .'/'. $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension;
        else
          $file = $cameralife->base_dir .'/'. $this->GetPref('photo_dir') .'/'. $photo->Get('path').$photo->Get('filename');
      }
      elseif ($type == 'original')
        $file = $cameralife->base_dir .'/'. $this->GetPref('photo_dir') .'/'. $photo->Get('path').$photo->Get('filename');
      elseif ($type == 'modified')
        $file = $cameralife->base_dir .'/'. $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension;
      elseif ($type == 'scaled')
        $file = $cameralife->base_dir .'/'. $this->GetPref('cache_dir').'/'.$photo->Get('id').'_600.'.$extension;
      elseif ($type == 'thumbnail')
        $file = $cameralife->base_dir .'/'. $this->GetPref('cache_dir').'/'.$photo->Get('id').'_150.'.$extension;

      if (!file_exists($file))
      {
        if ($type == 'original')
          $cameralife->Error("The photo <b>$file</b> cannot be found");
        $photo->GenerateThumbnail($photo->Get('modified') ? 'FALSE' : 'TRUE');
      }

      return array($file, FALSE, filemtime($file));
    }

    /**
    *  PutFile
    *
    *  Puts a new photo into the photostore. 
    *
    *  @param $photo - a target photo object
    *  @param $filename - the local filesystem file that we are putting
    *
    *  @return none
    */
    function PutFile($photo, $filename) 
    {
      global $cameralife;

      $this->mkdir_recursive($cameralife->base_dir .'/'. $this->GetPref('photo_dir') .'/'. $photo->Get('path'));
      copy($filename, $cameralife->base_dir .'/'. $this->GetPref('photo_dir') .'/'. $photo->Get('path').$photo->Get('filename'))
        or $cameralife->Error('Could not add photo: ' . $photo->Get('path').$photo->Get('filename'), __FILE__, __LINE__);
      chmod($cameralife->base_dir .'/'. $this->GetPref('photo_dir') .'/'. $photo->Get('path').$photo->Get('filename'), 0666);
    }

    /**
    *  PutThumbnails
    *
    *  Puts a scaled photo and thumbnail into the photostore. 
    *
    *  @param $photo - a target photo object
    *  @param $scaled - the file to insert
    *  @param $thumbnail - the file to insert
    *
    *  @return none
    */
    function PutThumbnails($photo, $scaled, $thumbnail) 
    {
      global $cameralife;

      $extension = $photo->extension;

      @rename($scaled, $cameralife->base_dir .'/'. $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_600.'.$extension);
      chmod($cameralife->base_dir .'/'. $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_600.'.$extension, 0666);
      @rename($thumbnail, $cameralife->base_dir .'/'. $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_150.'.$extension);
      chmod($cameralife->base_dir .'/'. $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_150.'.$extension, 0666);
    }

    # unnessary, user does not access files directly
    function SetPermissions() {}

    /**
    *  CheckThumbnails
    *
    *  Ensures that thumbnails exist
    *
    *  @param $photo - a target photo object
    *
    *  @return true if work was done
    */
    function CheckThumbnails($photo) 
    {
      global $cameralife;

      $path_parts = pathinfo($photo->Get('filename'));
      $extension = strtolower($path_parts['extension']);

      if (!file_exists($cameralife->base_dir .'/'. $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_600.'.$extension) ||
          !file_exists($cameralife->base_dir .'/'. $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_150.'.$extension) ||
          $photo->Get('modified') && !file_exists($cameralife->base_dir .'/'. $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension))
      {
        $photo->GenerateThumbnail();
        return TRUE;
      }
      return FALSE;
    }

    /**
    *  ModifyFile
    *
    *  Puts a modified version of a photo into the photostore. Passing
    *  NULL for the filename will revert the photo.
    *
    *  @param $photo - a target photo object
    *  @param $filename - the local filesystem file that we are putting
    *
    *  @return none
    */
    function ModifyFile($photo, $filename) 
    {
      global $cameralife;

      $path_parts = pathinfo($photo->Get('filename'));
      $extension = strtolower($path_parts['extension']);

      rename($filename, $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension)
        or $cameralife->Error('Rename error', __FILE__, __LINE__);
      chmod($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension, 0666);
      unlink($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_600.'.$extension);
      unlink($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_150.'.$extension);

      # if not lazy, update thumbnails
    }


    /**
    *  EraseFile
    *
    *  Erase a photo and associated scaled photos from the system
    *
    *  @param $photo - a target photo object
    *
    *  @return none
    */
    function EraseFile($photo) 
    {
      global $cameralife;

      $path_parts = pathinfo($photo->Get('filename'));
      $extension = strtolower($path_parts['extension']);

      $this->mkdir_recursive($cameralife->base_dir . '/' . $this->GetPref('deleted_dir') .'/'. $photo->Get('path'));
      @rename ($cameralife->base_dir . '/' . $this->GetPref('photo_dir') .'/'. $photo->Get('path').$photo->Get('filename'), $cameralife->base_dir . '/' . $this->GetPref('deleted_dir') .'/'. $photo->Get('path').$photo->Get('filename'));

      if ($photo->Get('modified'))
        @unlink($cameralife->base_dir . '/' . $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension);
      @unlink($cameralife->base_dir . '/' . $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_600.'.$extension);
      @unlink($cameralife->base_dir . '/' . $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_150.'.$extension);
    }

    /**
    *  ListFiles
    *
    *  Returns a list of all files in the photostore
    *
    *  @return an array of files, in the form 'path'=>basename(path)
    */
    function ListFiles($path = '', $recur=TRUE)
    {
      global $cameralife;
  
      $retval = array();
      $prefix = $cameralife->base_dir . '/' . $this->GetPref('photo_dir');
      if ($dir = opendir($prefix . '/' . $path))
      {
        $children = array();
        while (false !== ($file = readdir($dir)))
        {
          if ($file[0]=='.') continue;
          $photopath = $path.$file;
          if ($recur && is_dir($prefix . '/' . $photopath))
            $children[] = $photopath . '/';
          else if (is_file($prefix . '/' . $photopath))
            $retval[$photopath] = $file;
        }
        closedir($dir);
        sort($children);
        foreach($children as $child)
        {
          $retval += $this->ListFiles($child);
        }
      }
      else
      {
#        $cameralife->Error('Failed to open photo directory: '.$prefix . '/' . $path, __FILE__, __LINE__);
      }
      return $retval;
    }

    ##### PRIVATE

    function mkdir_recursive($pathname, $mode=0777)
    {
      is_dir(dirname($pathname)) || $this->mkdir_recursive(dirname($pathname), $mode);
      return is_dir($pathname) || @mkdir($pathname, $mode);
    }


  }
?>
