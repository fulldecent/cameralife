<?php
  /*
  *  This module stores files on a remote filesystem.
  *  That remote server must support public HTTP.
  *  Private files are delivered through this server.
  *  Lazy thumbnailing is not supported.
  *
  */
  /**
  *This module stores files on a remote filesystem.
   *@link http://fdcl.sourceforge.net/
  *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@access public
  *@copyright Copyright (c) 2001-2009 Will Entriken
  */
  
  /**This remotely located filesystem supports public HTTP
  *
  *  Private files are delivered through this server.
  *
  *  Lazy thumbnailing is not supported.
  */

  class RemoteServerPhotoStore extends PhotoStore
  {
    function RemoteServerPhotoStore() 
    {
      $this->name = 'RemoteServer';

      $this->preferences[] = array('name'=>'photo_dir', 'desc'=>'Main photo directory, can use ftp://, ssh2:// or anything your PHP can handle', 'type'=>'directory', 'default'=>'ftp://user:pass@localhost/images/photos');
      $this->preferences[] = array('name'=>'photo_dir_url', 'desc'=>'- The URL of the directory where this stuff is available for users.', 'type'=>'string', 'default'=>'http://localhost/images/photos');
      $this->preferences[] = array('name'=>'cache_dir', 'desc'=>'Camera Life data directory, same options as above', 'type'=>'directory', 'default'=>'ftp://user:pass@localhost/images/cache');
      $this->preferences[] = array('name'=>'cache_dir_url', 'desc'=>'- The URL of the directory where this stuff is available for users.', 'type'=>'string', 'default'=>'http://localhost/images/cache');
      $this->preferences[] = array('name'=>'deleted_dir', 'desc'=>'Deleted photos (...where they go when you "erase" them), same options as above', 'type'=>'directory', 'default'=>'ftp://user:pass@localhost/images/deleted');
    } 

    /**
    *  GetURL
    *
    *  Gets a URL for the client to access the selected resource.
    *  (Implementation note: make sure non-public photos do not have publicly accessible urls)
    *
    *  @param $photo - a target photo obeject
    *  @param $type - one of ('photo', 'scaled', 'thumbnail')
    *
    *  @return  URL
    */
    function GetURL($photo, $type='photo') 
    {
      global $cameralife;

      if ($photo->Get('status') == 0) #public
      {
        $extension = $photo->extension;

        if ($type=='photo')
        {  
          if ($photo->Get('modified'))
            return $this->GetPref('cache_dir_url').'/'.$photo->Get('id').'_mod.'.$extension;
          else
            return $this->GetPref('photo_dir_url').'/'.$photo->Get('path').$photo->Get('filename');
        }
        elseif ($type=='scaled')
          return $this->GetPref('cache_dir_url').'/'.$photo->Get('id').'_'.$cameralife->GetPref('scaledsize').'.'.$extension;
        else // ($type=='thumbnail')
          return $this->GetPref('cache_dir_url').'/'.$photo->Get('id').'_'.$cameralife->GetPref('thumbsize').'.'.$extension;
      }

      return FALSE; # request handled through media.php
    }

    /**
    *  GetFile
    *
    *  Gets a filename for the requested resource. It is downloaded if necessary.
    *
    *  @param $photo - a target photo object
    *  @param $type - one of ('photo', 'original', 'modified', 'scaled', 'thumbnail').  'photo' refers to the modified (if exists) otherwise original
    *
    *  @return an array with two elements:
    *     filename - string - the local file you requested
    *     temporary - boolean
    *     mtime - the time a file was last modified
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
        {
          $file = $this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension;
          $type = 'modified';
        }
        else
        {
          $file = $this->PhotoDir .'/'. $photo->Get('path').$photo->Get('filename');
          $type = 'original';
        }
      }
      elseif ($type == 'original')
        $file = $this->PhotoDir .'/'. $photo->Get('path').$photo->Get('filename');
      elseif ($type == 'modified')
        $file = $this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension;
      elseif ($type == 'scaled')
        $file = $this->CacheDir .'/'.$photo->Get('id').'_'.$cameralife->GetPref('scaledsize').'.'.$extension;
      elseif ($type == 'thumbnail')
        $file = $this->CacheDir .'/'.$photo->Get('id').'_'.$cameralife->GetPref('thumbsize').'.'.$extension;
      elseif (is_numeric($type))
      {
        $file = $this->CacheDir .'/'.$photo->Get('id').'_'.$type.'.'.$extension;
      }

/* assume consistent state (famous last words?)
      if (!file_exists($file))
      {
        if ($type == 'original')
          $cameralife->Error("The photo <b>$file</b> cannot be found");
        if ($type == 'modified')
        {
          $photo->Revert();
          $file = $this->PhotoDir .'/'. $photo->Get('path').$photo->Get('filename');
          if (!file_exists($file))
            $cameralife->Error("The photo <b>$file</b> cannot be found");
        }
        $photo->GenerateThumbnail($photo->Get('modified') ? 'FALSE' : 'TRUE');
      }
*/
      return array($file, FALSE, @filemtime($file));
    }

    /*
    *  PutFile
    *
    *  Puts a new photo into the photostore. 
    *
    *  @param $photo - a target photo object
    *  @param $filename - the local filesystem file that we are putting
    *
    *  @return none
    */
    /**
        *  Puts a new photo into the photostore. 
        *
        *  @param $photo - a target photo object
        *  @param $filename - the local filesystem file that we are putting
        *
        */
    
    function PutFile($photo, $filename) 
    {
      global $cameralife;

      $this->mkdir_recursive($this->GetPref('photo_dir') .'/'. $photo->Get('path'));
      @unlink($this->GetPref('photo_dir') .'/'. $photo->Get('path').$photo->Get('filename'));
      copy($filename, $this->GetPref('photo_dir') .'/'. $photo->Get('path').$photo->Get('filename'))
        or $cameralife->Error('Could not add photo: ' . $photo->Get('path').$photo->Get('filename'), __FILE__, __LINE__);
      $photo->GenerateThumbnail();
#      chmod($this->GetPref('photo_dir') .'/'. $photo->Get('path').$photo->Get('filename'), 0666);
    }

    /*
    *  PutThumbnails
    *
    *  Puts a a scaled photo and thumbnail into the photostore. 
    *
    *  @param $photo - a target photo object
    *  @param $thumbs - array of (size, file)
    *
    *  @return none
    */
    function PutThumbnails($photo, $thumbs) 
    {
      global $cameralife;

      $extension = $photo->extension;

      $this->mkdir_recursive($this->GetPref('cache_dir'));

      foreach ($thumbs as $size=>$file)
      {
        @unlink($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_'.$size.'.'.$extension);
        copy($file, $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_'.$size.'.'.$extension);
      }
    }

//TODO
    # hm??? any thounght?
    function SetPermissions() {}

    /**
    *  CheckThumbnails
    *
    *  Ensures that thumbnails exist
    *
    *  @param mixed $photo - a target photo object
    *
    *  @return true if work was done
    */
    function CheckThumbnails($photo)
    {
      global $cameralife;

      $extension = $photo->extension;

      if(!file_exists($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_'.$cameralife->GetPref('scaledsize').'.'.$extension))
      {
        $photo->GenerateThumbnail();
        return TRUE;
      }
      else
        return FALSE;
    }

    /*
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
     /*
        *  ModifyFile
        *
        *  Puts a modified version of a photo into the photostore. Passing NULL for the filename will revert the photo.
        *
        *  @param $photo - a target photo object
        *  @param $filename - the local filesystem file that we are putting
        *
        *  @return none
    */
    function ModifyFile($photo, $filename) 
    {
      global $cameralife;

      $extension = $photo->extension;

      @unlink($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension);
      if ($filename)
        copy($filename, $this->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension);
      elseif (file_exists($this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension))
        unlink($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension);
      @chmod($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension, 0666);
      @unlink($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_'.$cameralife->GetPref('scaledsize').'.'.$extension);
      @unlink($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_'.$cameralife->GetPref('thumbsize').'.'.$extension);
      unlink($filename);

      $photo->GenerateThumbnail($filename == NULL); // if we are reverting, let Generate know
    }


    /*
    *  EraseFile
    *
    *  Erase a photo and associated scaled photos from the system
    *
    *  @param $photo - a target photo object
    *
    *  @return none
    */
    
        /**
        * 
        *  Erase a photo and associated scaled photos from the system
        *
        *  @param mixed $photo - a target photo object
        *
        *  
        */
    function EraseFile($photo) 
    {
      global $cameralife;

      $extension = $photo->extension;

      $this->mkdir_recursive($this->GetPref('deleted_dir') .'/'. $photo->Get('path'));
      rename ($this->GetPref('photo_dir') .'/'. $photo->Get('path').$photo->Get('filename'), $this->GetPref('deleted_dir') .'/'. $photo->Get('path').$photo->Get('filename'));

      if ($photo->Get('modified'))
        unlink($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_mod.'.$extension);
      unlink($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_'.$cameralife->GetPref('scaledsize').'.'.$extension);
      unlink($this->GetPref('cache_dir') .'/'. $photo->Get('id').'_'.$cameralife->GetPref('thumbsize').'.'.$extension);
    }

    /*
    *  ListFiles
    *
    *  Returns a list of all files in the photostore
    *
    *  @return an array of files, in the form 'path'=>basename(path)
    */
     /**
        *  Returns a list of all files in the photostore
        *
        *  @return an array of files, in the form 'path'=>basename(path)
    */
    function ListFiles($path = '', $recur=TRUE)
    {
      global $cameralife;
  
      $retval = array();
      $prefix = $this->GetPref('photo_dir');
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
