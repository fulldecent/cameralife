<?php
  
class Folder extends Search
{
  var $path;

  # must construct with either a Photo or a Path
  function Folder($original = '')
  {
    global $cameralife;

    if (is_string($original)) # This a path
    {
      if (strpos($original, '..') !== false)
        die('Fatal error: folder.class.php: detected ".."');

      $this->path = $original;
    }
    elseif(get_class($original) == 'Photo') # Extract the path from this Photo
    {
      $this->path = $original->Get('path');
    }

    Search::Search('');
    $this->mySearchPhotoCondition = "path='".addslashes($this->path)."'";
    $this->mySearchFolderCondition = "path LIKE '".addslashes($this->path)."%/' AND path NOT LIKE '".$this->path."%/%/'";
  }

  # returns COUNT random decendants, or all if count=0
  function GetDescendants($count = 0)
  {
    global $cameralife;

    $result = array();
    $condition = "status=0 AND path LIKE '".$this->path."%/'";
    $family = $cameralife->Database->Select('photos','DISTINCT path',$condition,"LIMIT $count");
    while ($youngin = $family->FetchAssoc())
    {
      $result[] = new Folder($youngin['path']);
    }
    return $result;
  }

  function GetChildren()
  {
    global $cameralife;

    switch ($this->mySort)
    {
      case 'newest':    $sort = 'id desc'; break;
      case 'oldest':    $sort = 'id'; break;
      case 'az':        $sort = 'description'; break;
      case 'za':        $sort = 'description desc'; break;
      case 'popular':   $sort = 'hits desc'; break;
      case 'unpopular': $sort = 'hits'; break;
      case 'rand':      $sort = 'rand()'; break;
      default:          $sort = 'id desc';
    }

    $selection = "DISTINCT SUBSTRING_INDEX(SUBSTR(path,".(strlen($this->path)+1)."),'/',1) AS basename";
    $condition = "path LIKE '".addslashes($this->path)."%/' AND status=0";
    $extra =     "ORDER BY $sort ".$this->myLimit;
    $family = $cameralife->Database->Select('photos', $selection, $condition, $extra);

    $result = array();
    while ($youngin = $family->FetchAssoc())
      $result[] = new Folder($this->path . $youngin['basename'] . '/');
    return $result;
  }

  function GetIcon()
  {
    return array('href'=>'folder.php&#63;path='.$this->path, 'name'=>basename($this->path), 'image'=>'icon-folder');
  }

  function GetSmallIcon()
  {
    return array('href'=>'folder.php&#63;path='.$this->path, 
                 'name'=>"Folder ".basename($this->path),
                 'image'=>'small-folder');
  }

  function Path()
  {
    return $this->path;
  }

  function Basename()
  {
    return basename($this->path);
  }

  function Dirname()
  {
    return dirname($this->path);
  }

  // Private
  // Returns an array of files starting at $path
  // in the form 'path'=>basename(path)
  function walk_dir($path)
  {
    $retval = array();
    if ($dir = opendir($path)) {
      while (false !== ($file = readdir($dir)))
      {
        if ($file[0]==".") continue;
        if (is_dir($path."/".$file))
          $retval = array_merge($retval,walk_dir($path."/".$file));
        else if (is_file($path."/".$file))
          if (preg_match("/.jpg$/i",$file))
            $retval[$path."/".$file] = $file;
      }
      closedir($dir);
    }
    return $retval;
  }

//********** always recurse
//********** does not work on *this* folder
  // Makes the database consistent with what is actually in this folder
  // Returns any errors or warnings as an array of marked up html
  // Note: this does not use the pretty classes, it is optimized to
  //   edit the DB directly
  function Update($recursive=false)
  {
    $origdir = getcwd();
    chdir ($cameralife->base_dir);
    $retval = array();

    // Detecting modified and deleted files...
    $new_files = walk_dir($cameralife->preferences['core']['photo_dir']);
    $result = $cameralife->Database->Select('photos','id,filename,path,fsize');

    // Verify each photo in the DB
    while ($photo = $result->FetchAssoc())
    {
      $filename = $photo['filename'];
      $fullpath = $cameralife->preferences['core']['photo_dir'].'/'.$photo['path'].$filename;

      // Found in correct location
      if ($new_files[$fullpath])
      {
        $actualsize = filesize($fullpath);

        // Found, but changed
        if ($actualsize != $photo['fsize'])
        {
          $retval[] = "$fullpath was changed, flushing cache";
          $photoObj = new Photo($photo['id']);
          $photoObj->Revert();
          $photoObj->Destroy();
        }
        unset ($new_files[$fullpath]);
        continue;
      }

      // Look for a photo with the same name and filesize anywhere else
      $paths = array_keys($new_files, $filename);
      foreach ($paths as $path)
      {
        if (filesize($path) == $photo['fsize'])
        {
          $newpath=substr(dirname($path),strlen($cameralife->preferences['core']['photo_dir'])+1).'/';
          if ($newpath == '/')
            $newpath = '';

          $cameralife->Database->Update('photos',array('path'=>$newpath),'id='.$photo['id']);
          $retval[] = "$filename moved to $newpath";
          unset ($new_files[$path]);
          continue 2;
        }
      }

      // Photo not found anywhere
      $retval[] = "$filename was deleted from filesystem";
      $photoObj = new Photo($photo['id']);
      $photoObj->Erase();
    }

    // Looking for new files to index...
    foreach ($new_files as $new_file => $newbase)
    {
      $newpath=substr(dirname($new_file),strlen($cameralife->preferences['core']['photo_dir'])+1);
      if ($newpath) $newpath .= '/';

      $actualsize = filesize($new_file);

      $condition = "filename='".mysql_real_escape_string($newbase)."' and fsize=$actualsize";
      $result = $cameralife->Database->Select('photos','path',$condition);

      if ($photo = $result->FetchAssoc())
      {
        $a = file_get_contents($cameralife->preferences['core']['photo_dir'].'/'.$photo['path'].$newbase);
        $b = file_get_contents($new_file);

        $error = '';
        if ($a == $b)
          $error = 'Warning: Two photos in your photo directory are identical, please delete one ';
        else
          $error  = 'Warning: Two photos in your photo directory are suspiciously similar, please delete one ';
        $error .= $photo['path']."$newbase already exists, $newpath$newbase does not";
        $retval[] = $error;
        continue;
      }

      $retval[] = "Added $newpath$newbase\n";

      $photoObj = new Photo(array('filename'=>$newbase, 'path'=>$newpath));
      $photoObj->Destroy();
    }
  }
}

?>
