<?php
  
class Folder extends Search
{
  var $path;

  # must construct with either a Photo or a Path
  # choose sync to verify the Folder with whats actually on the disk
  function Folder($original = '', $sync=FALSE)
  {
    global $cameralife;

    if (is_string($original)) # This a path
    {
      if (strpos($original, '..') !== false)
        $cameralife->Error('Tried to access a path which contains  ..', __FILE__, __LINE__);

      $this->path = stripslashes($original);
    }
    elseif(get_class($original) == 'Photo') # Extract the path from this Photo
    {
      $this->path = $original->Get('path');
    }

    if($sync && !$this->Fsck())
      Folder::Update();

    Search::Search('');
    $this->mySearchPhotoCondition = "path='".addslashes($this->path)."'";
    $this->mySearchFolderCondition = "path LIKE '".addslashes($this->path)."%/' AND path NOT LIKE '".addslashes($this->path)."%/%/'";
  }

  # returns an array of Folders
  function GetAncestors()
  {
    $retval = array();

    if (strlen($this->path) > 1)
    {
      $retval[] = new Folder('', FALSE);
      
      foreach (explode("/",$this->path) as $dir)
      {
        if (!$dir) continue;
        $full_path=$full_path.$dir."/";
        if ($full_path == $this->path) continue;
        $retval[] = new Folder($full_path, FALSE);
      }
    }
    return $retval;
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
      $result[] = new Folder($youngin['path'], FALSE);
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
      $result[] = new Folder($this->path . $youngin['basename'] . '/', FALSE);
    return $result;
  }

  function GetIcon()
  {
    return array('href'=>'folder.php&#63;path='.$this->path, 'name'=>basename($this->path), 'image'=>'icon-folder');
  }

  function GetSmallIcon()
  {
    if (basename($this->path))
      $name = basename($this->path);
    else
      $name = '(Top level)';

    return array('href'=>'folder.php&#63;path='.$this->path, 
                 'name'=>"Folder $name",
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

  // Static function to make the DB match what is actually on the filesystem
  // Returns an array of any errors or warnings
  // Note: this does not use the pretty classes, it is optimized to
  //   edit the DB directly
  //
  // This works well under many strange circumstances you can put it in
  function Update()
  {
    global $cameralife;

    $retval = array();
    $new_files = $cameralife->PhotoStore->ListFiles();
    if (!count($new_files)) return array('Nothing was found in the photostore.');
    $result = $cameralife->Database->Select('photos','id,filename,path,fsize','','ORDER BY path, filename');

    // Verify each photo in the DB
    while ($photo = $result->FetchAssoc())
    {
      $filename = $photo['filename'];
      $photopath = $photo['path'].$filename;

      // Found in correct location
      if ($new_files[$photopath])
      {
        # Bonus code, if this is local, we can do more verification
        if ($cameralife->PhotoStore->name='local' && $photo['fsize'])
        {
          $photofile = $cameralife->base_dir.'/'.$cameralife->PhotoStore->GetPref('photo_dir')."/$photopath";
          $actualsize = filesize($photofile);

          // Found, but changed
          if ($actualsize != $photo['fsize'])
          {
            $retval[] = "$photopath was changed, flushing cache";
            $photoObj = new Photo($photo['id']);
            $photoObj->Revert();
            $photoObj->Destroy();
          }
        }

        unset ($new_files[$photopath]);
        continue;
      }

      // Look for a photo in the same place, but with the filename capitalization changed
      if ($new_files[strtolower($photopath)])
      {
        unset ($new_files[strtolower($photopath)]);
        continue;
      }

      if ($new_files[strtoupper($photopath)])
      {
        unset ($new_files[strtoupper($photopath)]);
        continue;
      }

      // Look for a photo with the same name and filesize anywhere else
      $candidatephotopaths = array_keys($new_files, $filename);
      foreach ($candidatephotopaths as $candidatephotopath)
      {
        # Bonus code
        if ($cameralife->PhotoStore->name='local')
        {
          $actualsize = filesize($cameralife->PhotoStore->GetPref('photo_dir') . '/' . $new_file);
          if ($actualsize != $photo['fsize'])
            continue;
        }

        $candidatedirname=dirname($candidatephotopath);
        if ($candidatedirname) $candidatedirname .= './';

        $cameralife->Database->Update('photos',array('path'=>$candidatedirname),'id='.$photo['id']);
        $retval[] = "$filename moved to $candidatedirname";
        unset ($new_files[$photopath]);

        # keep track of the number 0234 in like DSCN_0234.jpg
        $number = preg_replace('/[^\d]/','',$filename);
        if ($number > 1000)
          $lastmoved = array($number, $newpath);
        continue 2;
      }

      // If two photos with consecutive names are moved to another directory
      // AND one of them was modified outside of Camera Life
      // then this will find it
      //
      // (otherwise a photo that was moved and changed would be considered lost)
      foreach ($candidatephotopaths as $candidatephotopath)
      {
        $number = preg_replace('/[^\d]/','',$candidatephotopath);
        
        if ($number > 1000 && abs($number - $lastmoved[0])<5 && $newpath == $lastmoved[1])
        {
          $candidatedirname=dirname($candidatephotopath).'/';
          if ($candidatedirname) $candidatedirname .= './';

          $cameralife->Database->Update('photos',array('path'=>$candidatedirname),'id='.$photo['id']);
          $retval[] = "$photopath probably moved to $candidatedirname";
          unset ($new_files[$photopath]);
          $lastmoved = array($number, $candidatedirname);
          continue 2;
        }
        else
        {
          $str = $photo['path'].$photo['filename']." is missing, and $candidatephotopath was found, ";
          $str .= "they are not the same, I don't know what to do... ";
          $str .= "If they are the same, move latter to former, update, then move back.";
          $str .= "If they are different, move latter out of the photo directory, update and then move back.";

          $retval[] = $str;
          unset ($new_files[$photopath]);
          continue 2;
        }
      }

      // Photo not found anywhere
      $retval[] = "$photopath was deleted from filesystem";
      $photoObj = new Photo($photo['id']);
      $photoObj->Erase();
    }

    // $new_files now contains a list of existing files that are not in the database
    // We are looking for any excuse NOT to add them to the DB

    foreach ($new_files as $new_file => $newbase)
    {
      if (!preg_match("/.jpg$|.jpeg$|.png$/i",$newbase))
      {
        $retval[] = "Skipped $new_file because it is not a JPEG or PNG file";
        continue;
      }
     
      $newpath=dirname($new_file);
      if ($newpath) $newpath .= '/';

      # Bonus code
      if ($cameralife->PhotoStore->name='local')
      {
        $extra = ' and fsize='.$actualsize;
        $actualsize = filesize($cameralife->PhotoStore->GetPref('photo_dir') . '/' . $new_file);
      }
      else
        $extra = '';

      $condition = "filename LIKE '".mysql_real_escape_string($newbase)."' ".$extra;
      $result = $cameralife->Database->Select('photos','id, filename, path',$condition);

      // Is anything in the photostore too similar (given available information) to let this photo in?
      if ($photo = $result->FetchAssoc())
      {
        // With the case-insensitive LIKE above, this will handle files renamed only by case
        if(strcasecmp($photo['path'].$photo['filename'], $new_file) == 0)
        {
          $retval[] = $photo['path'].$photo['filename'].' was renamed to '.$new_file;
          $cameralife->Database->Update('photos',array('filename'=>$newbase),'id='.$photo['id']);
          continue;
        }

        # Bonus code
        $same = FALSE;
        if ($cameralife->PhotoStore->name='local')
        {
//TODO is this worth generalizing?
          $a = file_get_contents($cameralife->base_dir.'/'.$cameralife->PhotoStore->GetPref('photo_dir') . '/' . $photo['path'].$photo['filename']);
          $b = file_get_contents($cameralife->base_dir.'/'.$cameralife->PhotoStore->GetPref('photo_dir') . '/' . $new_file);
          if ($a == $b)
            $same = TRUE;
        }

        if ($same)
          $error = 'Two photos in your photo directory are identical, please delete one: ';
        else 
          $error  = 'Two photos in your photo directory are too similar, please delete one: ';
        $error .= $photo['path'].$photo['filename']." is in the system, $new_file is not";
        $retval[] = $error;
        continue;
      }

      # Bonus code
      if ($cameralife->PhotoStore->name='local')
      {
        $deletedfile = $cameralife->base_dir.'/'.$cameralife->PhotoStore->GetPref('deleted_dir')."/$newpath$newbase";
        if (file_exists($deletedfile) && filesize($deletedfile) == filesize($cameralife->base_dir.'/'.$cameralife->PhotoStore->GetPref('photo_dir') . '/' . $new_file))
        {
          $error = "A file that was added to the photostore $new_file is the same as ";
          $error .= "a file that was previoulsy deleted. Remove the new or the old file: ";
          $error .= $cameralife->preferences['core']['deleted_dir'].'/'.$newbase;
          $retval[] = $error;
          continue;
        }
      }

      $retval[] = "Added $new_file\n";

      $photoObj = new Photo(array('filename'=>$newbase, 'path'=>$newpath));
      # Don't need to add to the photostore, since its already there
      $photoObj->Destroy();
    }
    return $retval;
  }

  // Quickly checks if DB and FS are synched
  // returns true/false 
  function Fsck()
  {
    global $cameralife;

    $files = $cameralife->PhotoStore->ListFiles($this->path, FALSE);
    $fsphotos = $fsdirs = array();
    foreach ($files as $file)
    {
      if (preg_match("/.jpg$|.jpeg$|.png$/i",$file))
        $fsphotos[] = $file;
      else 
      {
        # Local optimization (hack?)
        if ($cameralife->PhotoStore->name='local')
        {
          if (!is_dir($cameralife->PhotoStore->GetPref('photo_dir') . '/' . $this->path . $file))
            continue;
        }

        $fsdirs[] = $file;
      }
    }

    $selection = "filename";
    $condition = "path = '".addslashes($this->path)."'";
    $result = $cameralife->Database->Select('photos', $selection, $condition);
    while ($row = $result->FetchAssoc())
    {
      $key = array_search($row['filename'], $fsphotos);
      if($key === FALSE)
        return FALSE;
      else
        unset ($fsphotos[$key]);
    }

    $selection = "DISTINCT SUBSTRING_INDEX(SUBSTR(path,".(strlen($this->path)+1)."),'/',1) AS basename";
    $condition = "path LIKE '".addslashes($this->path)."%/' AND status=0";
    $result = $cameralife->Database->Select('photos', $selection, $condition, $extra);
    while ($row = $result->FetchAssoc())
    {
      $key = array_search($row['basename'], $fsdirs);
      if($key === FALSE)
        return FALSE;
      else
        unset ($fsdirs[$key]);
    }

    return (count($fsphotos) + count($fsdirs) == 0);
  }
}

?>
