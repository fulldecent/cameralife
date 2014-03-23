<?php
/**
 * An implementation of FileStore that stores photos on your local filesystem
 * An example path is: '/folder/file.png', all paths start with '/'
 * @see FileStore
 *
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2014 Will Entriken
 * @access public
 */
class LocalFileStore extends FileStore
{
  function LocalFileStore() 
  {
    global $cameralife;

    $this->name = 'Local';

    $this->preferences[] = array('name'=>'photo_dir', 'desc'=>'Main photo directory', 'type'=>'directory', 'default'=>'images/photos');
    $this->preferences[] = array('name'=>'cache_dir', 'desc'=>'Data directory', 'type'=>'directoryrw', 'default'=>'images/cache');
    $this->preferences[] = array('name'=>'deleted_dir', 'desc'=>'Deleted photos (...where they go when you "erase" them)', 'type'=>'directoryrw', 'default'=>'images/deleted');

    $prevdir = getcwd();
    chdir($cameralife->base_dir);
    $this->PhotoDir = rtrim(realpath($this->GetPref('photo_dir')), '/');
    $this->CacheDir = rtrim(realpath($this->GetPref('cache_dir')), '/');
    $this->DeletedDir = rtrim(realpath($this->GetPref('deleted_dir')), '/');
    chdir($prevdir);
  } 

  /** 
   * getURL
   * We don't not have special URLs to access the photos. 
   * The parent class defaults to returning FALSE.
   */

  /**
   * Gets a local filename for the requested resource. It is downloaded if necessary.
   *
   * @param $bucket - one of ('photo', 'other')
   * @param $path - the location of the stored file
   * @return an array with these elements:
   *     filename - string - the local file you requested
   *     temporary - boolean
   *     mtime - unix time that this file was modified
   *
   * If temporary is TRUE, the caller is responsible for deleting this file when done.
   */
  function getFile($bucket, $path)
  {
    global $cameralife;
    $fullpath = '';
    if ($bucket == 'photo')
      $fullpath = $this->PhotoDir.$path;
    else if ($bucket == 'other')
      $fullpath = $this->CacheDir.$path;
    else if ($bucket == 'deleted')
      $fullpath = $this->DeletedDir.$path;
    else
      $cameralife->error("The FileStore bucket <b>$bucket</b> is invalid");

    if (!file_exists($fullpath))
      $cameralife->error("The FileStore file cannot be found");

    return array($fullpath, FALSE, filemtime($fullpath));
  }

  /**
   * Save a file to the filestore
   *
   * @param $bucket - one of ('photo', 'other')
   * @param $path - the location of the stored file
   * @param $file - the local file which is to be put in the store
   * @param $secure - if secure, ensure this file will not be publicly accessible
   * @return none
   */
  function putFile($bucket, $path, $file, $secure=0)
  {
    global $cameralife;
    $fullpath = '';
    if ($bucket == 'photo')
      $fullpath = $this->PhotoDir.$path;
    else if ($bucket == 'other')
      $fullpath = $this->CacheDir.$path;
    else if ($bucket == 'deleted')
      $fullpath = $this->DeletedDir.$path;
    else
      $cameralife->error("The FileStore bucket <b>$bucket</b> is invalid");
    
    @mkdir(dirname($fullpath, 0666, true));
    rename($file, $fullpath);
  }

  /**
   * Sets whether the file must be secure
   *
   * @param $bucket - one of ('photo', 'other')
   * @param $path - the location of the stored file
   * @param $file - the local file which is to be put in the store
   * @param $secure - if secure, ensure this file will not be publicly accessible
   * @return none
   */
  function setPrivate($bucket, $path, $secure)
  {
die('NOT YET');
    return;
  }

  /**
   * erase a file
   *
   * @param $bucket - one of ('photo', 'other')
   * @param $path - the location of the stored file, starts with '/'
   *
   * @return none
   */
  function eraseFile($bucket, $path)
  {
    global $cameralife;
    $fullpath = '';
    if ($bucket == 'photo')
      $fullpath = $this->PhotoDir.$path;
    else if ($bucket == 'other')
      $fullpath = $this->CacheDir.$path;
    else if ($bucket == 'deleted')
      $fullpath = $this->DeletedDir.$path;
    else
      $cameralife->error("The FileStore bucket <b>$bucket</b> is invalid");
    unlink($fullpath);
  }

  /**
   * Returns a list of all files in the filestore
   *
   * @param $bucket - one of ('photo', 'other')
   * @param $path - the location of the stored file, starts with '/'
   * @param $recursive - whether to list recursively
   * @return an array of files, in the form 'path'=>basename(path)
   */
  function listFiles($bucket, $path='/', $recursive=TRUE)
  {
    global $cameralife;
    $fullpath = '';
    if ($bucket == 'photo')
      $fullpath = $this->PhotoDir.$path;
    else if ($bucket == 'other')
      $fullpath = $this->CacheDir.$path;
    else if ($bucket == 'deleted')
      $fullpath = $this->DeletedDir.$path;
    else
      $cameralife->error("The FileStore bucket <b>$bucket</b> is invalid");

    #FEATURE // add " NOCL" to the end of a folder to exclude it
    if (strpos($path, ' NOCL') !== FALSE)
      return array();

    $retval = array();
    if ($dir = @opendir($fullpath))
    {
      $children = array();
      while (false !== ($file = readdir($dir)))
      {
        if ($file[0]=='.') continue;
        if (is_file($fullpath.$file))
          $retval[$path.$file] = $file;
        else if ($recursive && is_dir($fullpath.$file))
          $children[] = $path.$file.'/';
      }
      closedir($dir);
      sort($children);
      foreach($children as $child)
        $retval += $this->listFiles($bucket, $child, TRUE);
    }
    else
    {
      $cameralife->error('Failed to open file directory');
    }
    return $retval;
  }
}
?>
