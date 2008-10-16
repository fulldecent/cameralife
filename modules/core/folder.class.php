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
    $this->mySearchPhotoCondition = "path='".mysql_real_escape_string($this->path)."'";
    $this->mySearchFolderCondition = "path LIKE '".mysql_real_escape_string($this->path)."%/' AND path NOT LIKE '".addslashes($this->path)."%/%/'";
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

  function GetIcon($size='large')
  {
    global $cameralife;
    $retval = array();

    if ($cameralife->GetPref('rewrite') == 'yes')
      $retval['href'] = $cameralife->base_url.'/folders/'.$this->path;
    else
      $retval['href'] = $cameralife->base_url.'/folder.php&#63;path='.$this->path;
    
    if (basename($this->path))
      $retval['name'] = basename($this->path);
    else
      $retval['name'] = '(Top level)';

    if ($size=='large')
      $retval['image'] = $cameralife->IconURL('folder');
    else
      $retval['image'] = $cameralife->IconURL('small-folder');

    return $retval;
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
  function array_isearch($str, $array) 
  {
    foreach($array as $k => $v) 
      if(strcasecmp($str, $v) == 0) return $k;
    return false;
  }

  // Make the DB match what is actually on the filesystem
  // Returns an array of any errors or warnings
  // Note: this does not use the pretty classes, it is optimized to
  //   access/edit the DB directly
  //
  // We don't keep hashes of the photos (I think it's too slow). Instead,
  // we use a whole lot of logic to compare the expected database with the
  // actual filesystem to find missing photos. NO MAN LEFT BEHIND.
  //
  // This works well under many strange circumstances, bring it!
  function Update()
  {
    global $cameralife;

    $retval = array();
    $new_files = $cameralife->PhotoStore->ListFiles();
    if (!is_array($new_files) || !count($new_files)) return array('Nothing was found in the photostore.');
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
        if ($cameralife->GetPref('photostore')=='local' && $photo['fsize'])
        {
          $photofile = $cameralife->PhotoStore->PhotoDir."/$photopath";
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
 
      # Was photo renamed lcase?
      if ($filename != strtolower($filename))
      {
        $candidatephotopaths = array_keys($new_files, strtolower($filename));

        foreach ($candidatephotopaths as $candidatephotopath)
        {
          $candidatedirname=dirname($candidatephotopath);
          $candidatefilename=dirname($candidatephotopath);
          if ($candidatedirname) $candidatedirname .= '/';
          if ($candidatedirname == './') $candidatedirname = '';
          if ($photo['path'] == $candidatedirname)
          {
            unset ($new_files[$candidatephotopath]);
            $cameralife->Database->Update('photos',array('filename'=>$candidatefilename),'id='.$photo['id']);
            continue 2;
          }
        }
      }

      # Was photo renamed ucase?
      if ($filename != strtoupper($filename))
      {
        $candidatephotopaths = array_keys($new_files, strtoupper($filename));

        foreach ($candidatephotopaths as $candidatephotopath)
        {
          $candidatedirname=dirname($candidatephotopath);
          $candidatefilename=dirname($candidatephotopath);
          if ($candidatedirname) $candidatedirname .= '/';
          if ($candidatedirname == './') $candidatedirname = '';
          if ($photo['path'] == $candidatedirname)
          {
            unset ($new_files[$candidatephotopath]);
            $cameralife->Database->Update('photos',array('filename'=>$candidatefilename),'id='.$photo['id']);
            continue 2;
          }
        }
      }

      // Look for a photo with the same name and filesize anywhere else
      $candidatephotopaths = array_keys($new_files, $filename);
      foreach ($candidatephotopaths as $candidatephotopath)
      {
        # Bonus code
        if ($cameralife->GetPref('photostore')=='local')
        {
          $actualsize = filesize($cameralife->PhotoStore->PhotoDir . '/' . $candidatephotopath);
          if ($actualsize != $photo['fsize'])
            continue;
        }

        $candidatedirname=dirname($candidatephotopath);
        if ($candidatedirname) $candidatedirname .= '/';
        if ($candidatedirname == './') $candidatedirname = '';

        $cameralife->Database->Update('photos',array('path'=>$candidatedirname),'id='.$photo['id']);
        $retval[] = "$filename moved to $candidatedirname";
        unset ($new_files[$candidatephotopath]);

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
          if ($candidatedirname) $candidatedirname .= '/';
          if ($candidatedirname=='./') $candidatedirname = '';

          $cameralife->Database->Update('photos',array('path'=>$candidatedirname),'id='.$photo['id']);
          $retval[] = "$photopath probably moved to $candidatedirname";
          unset ($new_files[$candidatephotopath]);
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
#          unset ($new_files[$candidatephotopath]); # needed?
          continue 2;
        }
      }

      // Photo not found anywhere
      $retval[] = "$photopath was deleted from filesystem";
      $photoObj = new Photo($photo['id']);
      $photoObj->Erase();
    }

    //
    // $new_files now contains a list of existing files that are not in the database
    // We are looking for any excuse NOT to add them to the DB
    //

    foreach ($new_files as $new_file => $newbase)
    {
      if (preg_match("/^picasa.ini|digikam3.db$/i",$newbase))
        continue;
      if (!preg_match("/.jpg$|.jpeg$|.png$/i",$newbase))
      {
        $retval[] = "Skipped $new_file because it is not a JPEG or PNG file";
        continue;
      }
     
      $newpath=dirname($new_file);
      if ($newpath) $newpath .= '/';
      if ($newpath=='./') $newpath = '';

      # Bonus code
      if ($cameralife->GetPref('photostore')=='local')
      {
        $actualsize = filesize($cameralife->PhotoStore->PhotoDir . '/' . $new_file);
        $extra = ' AND (fsize='.$actualsize.' OR fsize IS NULL)';
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
        if ($cameralife->GetPref('photostore')=='local')
        {
          $a = file_get_contents($cameralife->PhotoStore->PhotoDir . '/' . $photo['path'].$photo['filename']);
          $b = file_get_contents($cameralife->PhotoStore->PhotoDir . '/' . $new_file);
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
      if ($cameralife->GetPref('photostore')=='local')
      {
        $deletedfile = $cameralife->PhotoStore->DeletedDir ."/$newpath$newbase";
        if (file_exists($deletedfile) && filesize($deletedfile) == filesize($cameralife->PhotoStore->PhotoDir . '/' . $new_file))
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
    if(!is_array($files)) return FALSE;

    $fsphotos = $fsdirs = array();
    foreach ($files as $file)
    {
      if (preg_match("/.jpg$|.jpeg$|.png$/i",$file))
        $fsphotos[] = $file;
      else 
      {
        # Local optimization (hack?)
        if ($cameralife->GetPref('photostore')=='local')
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
