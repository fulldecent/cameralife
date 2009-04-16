<?php
/**
*<b>Secured method for storing and sharing your photos</b>
*
*Enables you to
*<ul>
*<li>Protect your photos database</li>
*<li>Allow viewing of only those photos authorized by you.
*Friends on your photo share list don't see the filename, so they can't guess a private filename. </li>
*<li>Allow authorized access to private photos.</li>
*</ul>
*Implementation Note : This module stores files on the local filesystem, and delivers them via another script.
  *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  *@link http://fdcl.sourceforge.net
*/
/**
*<b>This class belongs to module.inc. Stores photos on your local machine(laptop,desktop,server)</b>
*@todo To make this platform-independent
*<code>if (preg_match('|^/|', $this->GetPref('photo_dir')))
* $this->PhotoDir = rtrim($this->GetPref('photo_dir'), '/');
 *else
*  $this->PhotoDir = rtrim($cameralife->base_dir .'/'. $this->GetPref('photo_dir'), '/');
 *if (preg_match('|^/|', $this->GetPref('cache_dir')))
  *  $this->CacheDir = rtrim($this->GetPref('cache_dir'), '/');
  *else
  *  $this->CacheDir = rtrim($cameralife->base_dir .'/'. $this->GetPref('cache_dir'), '/');
  * if (preg_match('|^/|', $this->GetPref('deleted_dir')))
  *    $this->DeletedDir = rtrim($this->GetPref('deleted_dir'), '/');
  *  else
  *    $this->DeletedDir = rtrim($cameralife->base_dir .'/'. $this->GetPref('deleted_dir'), '/');
  * </code>
 */
  class LocalPhotoStore extends PhotoStore
  {
    function LocalPhotoStore() 
    {
      global $cameralife;

      $this->name = 'Local';

      $this->preferences[] = array('name'=>'photo_dir', 'desc'=>'Main photo directory', 'type'=>'directory', 'default'=>'images/photos');
      $this->preferences[] = array('name'=>'cache_dir', 'desc'=>'Camera Life data directory', 'type'=>'directory', 'default'=>'images/cache');
      $this->preferences[] = array('name'=>'deleted_dir', 'desc'=>'Deleted photos (...where they go when you "erase" them)', 'type'=>'directory', 'default'=>'images/deleted');
if (preg_match('|^/|', $this->GetPref('photo_dir')))
 $this->PhotoDir = rtrim($this->GetPref('photo_dir'), '/');
 else
  $this->PhotoDir = rtrim($cameralife->base_dir .'/'. $this->GetPref('photo_dir'), '/');
 if (preg_match('|^/|', $this->GetPref('cache_dir')))
        $this->CacheDir = rtrim($this->GetPref('cache_dir'), '/');
      else
        $this->CacheDir = rtrim($cameralife->base_dir .'/'. $this->GetPref('cache_dir'), '/');

     if (preg_match('|^/|', $this->GetPref('deleted_dir')))
        $this->DeletedDir = rtrim($this->GetPref('deleted_dir'), '/');
      else
        $this->DeletedDir = rtrim($cameralife->base_dir .'/'. $this->GetPref('deleted_dir'), '/');
        

    } 

    /** 
    *	Gets a URL for your friends to access a photo as authorized by you.
    * <ul>
    * <li> If FALSE is returned, the caller must construct a URL to media.php</li>     
    * <li> That page will call function GetFile()and display the photo to the user.</li>
    * </ul>
    *  
    * <b> Note </b>
    * DO NOT assign  publicly accessible urls to non-public photos 
    * @see photstore/module.php
    *
    *  @param mixed $photo - A target photo object
    *  @param string $type - Choose amongst('photo', 'scaled', 'thumbnail')
    *  @return string URL or boolean FALSE
    *  @see media.php
    */
    
    
    function GetURL($photo, $type='photo') 
    {
    
      return FALSE; 
      
    }

    /**
      *Gets the filename for the photo as stored on your local machine.
      *If the photo does not exist on your local machine it is downloaded from the cameralife server 
      *'photo' refers to the modified (if exists) otherwise original
     *@return an array with three elements
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
          $file = $this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension;
        else
          $file = $this->PhotoDir .'/'. $photo->Get('path').$photo->Get('filename');
      }
      elseif ($type == 'original')
        $file = $this->PhotoDir .'/'. $photo->Get('path').$photo->Get('filename');
      elseif ($type == 'modified')
        $file = $this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension;
      elseif ($type == 'scaled')
        $file = $this->CacheDir .'/'.$photo->Get('id').'_600.'.$extension;
      elseif ($type == 'thumbnail')
        $file = $this->CacheDir .'/'.$photo->Get('id').'_150.'.$extension;

      if (!file_exists($file))
      {
        if ($type == 'original')
          $cameralife->Error("The photo <b>$file</b> cannot be found");
        $photo->GenerateThumbnail($photo->Get('modified') ? 'FALSE' : 'TRUE');
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
    *  @param string $scaled - The file to insert
    *  @param mixed $thumbnail - The file to insert
    *
    *  @return none
    */
    function PutThumbnails($photo, $scaled, $thumbnail) 
    {
      global $cameralife;

      $extension = $photo->extension;

      @rename($scaled, $this->CacheDir .'/'. $photo->Get('id').'_600.'.$extension);
      chmod($this->CacheDir .'/'. $photo->Get('id').'_600.'.$extension, 0666);
      @rename($thumbnail, $this->CacheDir .'/'. $photo->Get('id').'_150.'.$extension);
      chmod($this->CacheDir .'/'. $photo->Get('id').'_150.'.$extension, 0666);
    }

   // @internal unnessary, user does not access files directly
    function SetPermissions() {}

    /**
     *  Ensures that thumbnails exist
    *
    *  @param mixed $photo A target photo object
    *
    *  @return boolean TRUE On successful completion of task
    */
    function CheckThumbnails($photo) 
    {
      global $cameralife;

      $extesnion = $photo->extension;

      if (!file_exists($this->CacheDir .'/'. $photo->Get('id').'_600.'.$extension) ||
          !file_exists($this->CacheDir .'/'. $photo->Get('id').'_150.'.$extension) ||
          $photo->Get('modified') && !file_exists($this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension))
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
      @unlink($this->CacheDir .'/'. $photo->Get('id').'_600.'.$extension);
      @unlink($this->CacheDir .'/'. $photo->Get('id').'_150.'.$extension);
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

      $extesnion = $photo->extension;

      $this->mkdir_recursive($this->DeletedDir .'/'. $photo->Get('path'));
      @rename ($this->PhotoDir .'/'. $photo->Get('path').$photo->Get('filename'), $this->DeletedDir .'/'. $photo->Get('path').$photo->Get('filename'));

      if ($photo->Get('modified'))
        @unlink($this->CacheDir .'/'. $photo->Get('id').'_mod.'.$extension);
      @unlink($this->CacheDir .'/'. $photo->Get('id').'_600.'.$extension);
      @unlink($this->CacheDir .'/'. $photo->Get('id').'_150.'.$extension);
    }

    /**
         *  Returns a list of all files in the photostore
    *
    *  @return an array of files, in the form 'path'=>basename(path)
    */
    function ListFiles($path = '', $recur=TRUE)
    {
      global $cameralife;
  
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
      return is_dir($pathname) || @mkdir($pathname, $mode);
    }


  }
?>
