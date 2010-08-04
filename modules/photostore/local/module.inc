<?php
/**
 * An implementation of PhotoStore that stores photos on your local filesystem. 
 * @see PhotoStore
 *
 * @version 2.6.3b3
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 * @link http://fdcl.sourceforge.net
 */
class LocalPhotoStore extends PhotoStore
{
  function LocalPhotoStore() 
  {
    global $cameralife;

    $this->name = 'Local';

    $this->preferences[] = array('name'=>'photo_dir', 'desc'=>'Main photo directory', 'type'=>'directory', 'default'=>'images/photos');
    $this->preferences[] = array('name'=>'cache_dir', 'desc'=>'Camera Life data directory', 'type'=>'directoryrw', 'default'=>'images/cache');
    $this->preferences[] = array('name'=>'deleted_dir', 'desc'=>'Deleted photos (...where they go when you "erase" them)', 'type'=>'directoryrw', 'default'=>'images/deleted');

    $prevdir = getcwd();
    chdir($cameralife->base_dir);
    $this->PhotoDir = rtrim(realpath($this->GetPref('photo_dir')), '/');
    $this->CacheDir = rtrim(realpath($this->GetPref('cache_dir')), '/');
    $this->DeletedDir = rtrim(realpath($this->GetPref('deleted_dir')), '/');
    chdir($prevdir);
  } 

  /** 
   * GetURL
   * LocalPhotoStore does not have special URLs to access the photos. 
   * The parent class defaults to returning FALSE.
   */

  /**
   * Gets the filename for the photo as stored on your local machine.
   * If the photo does not exist on your local machine it is downloaded from the cameralife server 
   * 'photo' refers to the modified (if exists) otherwise original
   * @return an array with three elements
   *<ul>
   *<li>string filename - The local file you requested</li>
   *<li>boolean temporary</li>
   *<li>integer mtime - Time at which a photo was last modified</li>
   *</ul>
   * If temporary is set, the caller is responsible for deleting this file when done.
   *@param mixed  $photo - A target photo object
   *@param string $type - One of ('photo', 'original', 'modified', 'scaled', 'thumbnail')
    *@return mixed array with three elements
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
        $photo->GenerateThumbnail();
      }

      return array($file, FALSE, filemtime($file));
    }

    /**
    *  Function PutFile
    *
    *  <ul>
    *	<li>-Puts a new photo into the photostore.</li>
    *	<li>-Appropriate permissions should be set up for the photo.</li>
    *   <li>-The file $filename does not get modified or deleted.<li>
    * </ul>
    *  @param $photo - A target photo object
    *  @param $filename - The local filesystem file that we are assigning
    *
    *  @return none
    */
    function PutFile($photo, $filename) 
    {
      global $cameralife;

      $this->mkdir_recursive($this->PhotoDir .'/'. $photo->Get('path'));
      copy($filename, $this->PhotoDir .'/'. $photo->Get('path').$photo->Get('filename'))
        or $cameralife->Error('Could not add photo: ' . $photo->Get('path').$photo->Get('filename') . ' from '.$filename, __FILE__, __LINE__);
      chmod($this->PhotoDir .'/'. $photo->Get('path').$photo->Get('filename'), 0666);
    }

    /**
    
    *
    *  Puts a scaled photo and thumbnail into the photostore. 
    *
    *  @param mixed $photo - A target photo object
    *  @param array $thumbs - (size, file)
    *
    *  @return none
    */
    function PutThumbnails($photo, $thumbs) 
    {
      global $cameralife;

      $extension = $photo->extension;

      foreach ($thumbs as $size=>$file)
      {
        @rename($file, $this->CacheDir .'/'. $photo->Get('id').'_'.$size.'.'.$extension);
        chmod($this->CacheDir .'/'. $photo->Get('id').'_'.$size.'.'.$extension, 0666);
      }
    }

   // @internal unnessary, user does not access files directly
    function SetPermissions() {}

    /**
     *  Ensures that thumbnails exist
     *
     *  @param mixed $photo A target photo object
     *
     *  @return boolean TRUE if thumbnails were created
     */
    function CheckThumbnails($photo) 
    {
      global $cameralife;

      $extension = $photo->extension;

      ///TODO why are there NULLs and 0s in the database?
      if (!file_exists($this->CacheDir .'/'. $photo->Get('id').'_'.$cameralife->GetPref('scaledsize').'.'.$extension) ||
          !file_exists($this->CacheDir .'/'. $photo->Get('id').'_'.$cameralife->GetPref('thumbsize').'.'.$extension) ||
          (($photo->Get('modified')!=NULL&&($photo->Get('modified')>0)) && !file_exists($this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension)))
      {
        $photo->GenerateThumbnail();
        return TRUE;
      }
      return FALSE;
    }

    /**
        *  Puts a modified version of a photo into the photostore. 
    *
    *<ul>
    *<li>-Passing NULL for the filename deletes cached copies of the photo.</li>
    *<li>-All further changes will utilize the orginal photo on the cameralife server</li>
    *</ul>
    *  @param mixed $photo - A target photo object
    *  @param $filename - The local filesystem file that we are assigning
    *
    *  @return none
    */
    function ModifyFile($photo, $filename) 
    {
      global $cameralife;

      $extension = $photo->extension;
 
      if($filename)
      {
        rename($filename, $this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension)
          or $cameralife->Error('Rename error', __FILE__, __LINE__);
        chmod($this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension, 0666);
      }
      elseif (file_exists($this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension))
        unlink($this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension);
      @unlink($this->CacheDir .'/'. $photo->Get('id').'_'.$cameralife->GetPref('scaledsize').'.'.$extension);
      @unlink($this->CacheDir .'/'. $photo->Get('id').'_'.$cameralife->GetPref('thumbsize').'.'.$extension);
    }


    /**
     *  Erase a photo and associated scaled photos from the system
    *
    *  @param mixed $photo A target photo object
    *
    *  @return none
    */
    function EraseFile($photo) 
    {
      global $cameralife;

      $extension = $photo->extension;

      $this->mkdir_recursive($this->DeletedDir .'/'. $photo->Get('path'));
      @rename ($this->PhotoDir .'/'. $photo->Get('path').$photo->Get('filename'), $this->DeletedDir .'/'. $photo->Get('path').$photo->Get('filename'));

      if ($photo->Get('modified'))
        @unlink($this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension);
      @unlink($this->CacheDir .'/'. $photo->Get('id').'_'.$cameralife->GetPref('scaledsize').'.'.$extension);
      @unlink($this->CacheDir .'/'. $photo->Get('id').'_'.$cameralife->GetPref('thumbsize').'.'.$extension);
    }

    /**
         *  Returns a list of all files in the photostore
    *
    *  @return an array of files, in the form 'path'=>basename(path)
    */
    function ListFiles($path = '', $recur=TRUE)
    {
      global $cameralife;

      #FEATURE // add " NOCL" to the end of a folder to exclude it
      if (strpos($path, ' NOCL') !== FALSE)
        return array();
  
      $retval = array();
      $prefix = $this->PhotoDir;
      if ($dir = @opendir($prefix . '/' . $path))
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

   
 #private
    function mkdir_recursive($pathname, $mode=0777)
    {
      is_dir(dirname($pathname)) || $this->mkdir_recursive(dirname($pathname), $mode);
      return is_dir($pathname) || mkdir($pathname, $mode);
    }


  }
?>
