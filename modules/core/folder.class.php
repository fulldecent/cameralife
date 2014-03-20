<?php
/**
 * Folder class.
 * Access folders on the file system as objects
 *
 * @author Will Entriken <cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2001-2014 Will Entriken
 * @extends Search
 */
class Folder extends Search
{
  /**
   * path
   * Like: '/' or '/afolder' or '/parent/child'
   *
   * @var string
   * @access public
   */
  public $path;

  /**
  * This function should be constructed with either of the parameters Photo or Path.
  * Use 'sync' to compare and verify Folder and disk content
  *
  * <b>Optional </b> When is the latest photo in this folder from, unixtime
  */

//TODO REMOVE THE SYNC AND DATE PARAM
  public function Folder($path='/', $sync=FALSE, $date=NULL)
  {
    global $cameralife;
    $this->path = $path;
    if (!strlen($path)) $this->path='/';
    $this->date = $date;

    if($sync && !$this->Fsck())
      Folder::Update();

    Search::Search('');
//todo use bind here, add a bind parameter to Search
    @$this->mySearchPhotoCondition = "path='".mysql_real_escape_string($this->path)."'";
    $this->mySearchAlbumCondition = "FALSE";
    @$this->mySearchFolderCondition = "path LIKE '".mysql_real_escape_string($this->path)."%/' AND path NOT LIKE '".addslashes($this->path)."%/%/'";
  }

  public function GetPrevious()
  {
    global $cameralife;
    if ($this->myStart > 0) {
      if ($cameralife->GetPref('rewrite') == 'yes')
        $href = $cameralife->base_url.'/folders'.str_replace(" ","%20",$this->path); 
      else
        $href = $cameralife->base_url.'/folder.php&#63;path='.str_replace(" ","%20",$this->path);
      $href = AddParam($href, 'start', $this->myStart - $this->myLimitCount);
      return $href;
    }
    return NULL;
  }

  public function GetAncestors()
  {
    $retval = array();
    $path = $this->path;
    while ($path != '/') {
      $path = dirname($path);
      $retval[] = new Folder($path);
    }
    return array_reverse($retval);
  }

  /**
   * @return some decentants, or all if count==0
   */
  public function GetDescendants($count = 0)
  {
    global $cameralife;
    switch ($this->mySort) {
      case 'newest':    $sort = 'created desc'; break;
      case 'oldest':    $sort = 'created'; break;
      case 'az':        $sort = 'path'; break;
      case 'za':        $sort = 'path desc'; break;
      case 'popular':   $sort = 'hits desc'; break;
      case 'unpopular': $sort = 'hits'; break;
      case 'rand':      $sort = 'rand()'; break;
      default:          $sort = 'id desc';
    }

    $result = array();
    $selection = 'DISTINCT path';
    $condition = "status=0 AND path LIKE '".$this->path."_%'"; //TODO THIS IS ACTUALLY WRONG
    $extra =     "ORDER BY $sort LIMIT $count";
    $family = $cameralife->Database->Select('photos', $selection, $condition, $extra);
    while ($youngin = $family->FetchAssoc())
      $result[] = new Folder($youngin['path'], FALSE);

    return $result;
  }

  public function GetChildren()
  {
    global $cameralife;
    switch ($this->mySort) {
      case 'newest':    $sort = 'id desc'; break;
      case 'oldest':    $sort = 'id'; break;
      case 'az':        $sort = 'path'; break;
      case 'za':        $sort = 'path desc'; break;
      case 'popular':   $sort = 'hits desc'; break;
      case 'unpopular': $sort = 'hits'; break;
      case 'rand':      $sort = 'rand()'; break;
      default:          $sort = 'id desc';
    }

    $selection = "DISTINCT SUBSTRING_INDEX(SUBSTR(path,".(strlen($this->path)+1)."),'/',1) AS basename";
    $condition = "path LIKE '".addslashes($this->path)."/%' AND status=0";
    $extra =     "ORDER BY $sort ".$this->myLimit;
    $family = $cameralife->Database->Select('photos', $selection, $condition, $extra);

    $result = array();
    while ($youngin = $family->FetchAssoc())
      $result[] = new Folder($this->path . $youngin['basename'] . '/', FALSE);

    return $result;
  }

  public function GetIcon($size='large')
  {
    global $cameralife;
    $retval = array();

    if ($cameralife->GetPref('rewrite') == 'yes')
      $retval['href'] = $cameralife->base_url.'/folders'.str_replace(" ","%20",$this->path); 
    else
      $retval['href'] = $cameralife->base_url.'/folder.php&#63;path='.str_replace(" ","%20",$this->path);

    if (basename($this->path))
      $retval['name'] = $this->Basename();
    else
      $retval['name'] = '(All photos)';

    if ($size=='large')
      $retval['image'] = $cameralife->IconURL('folder');
    else
      $retval['image'] = $cameralife->IconURL('small-folder');

    $retval['date'] = $this->date;

    return $retval;
  }

//TODO: NO ENTITIES
  public function Path()
  {
    return htmlentities($this->path);
  }

  public function Basename()
  {
    return basename(htmlentities($this->path));
  }

  public function Dirname()
  {
    return dirname(htmlentities($this->path));
  }

  /**
  * @access private
  */
  public function array_isearch($str, $array)
  {
    foreach($array as $k => $v)
      if(strcasecmp($str, $v) == 0) return $k;

    return false;
  }

  /**
   * Updates the DB to match actual contents of photo bucket from filestore.
   * Returns an array of errors or warning.
   * Tries very hard to avoid creating a new record and deleting an old if in fact the 
   * photo was simply moved.
   */
  public static function Update()
  {
    global $cameralife;

    $retval = array();
    $filesInStoreNotYetMatchedToDB = $cameralife->FileStore->ListFiles('photo');
    if (!count($filesInStoreNotYetMatchedToDB)) return array('Nothing was found in the filestore.');
    $result = $cameralife->Database->Select('photos','id,filename,path,fsize','','ORDER BY path,filename');

    // Verify each photo in the DB
    while ($photo = $result->FetchAssoc()) {    
//TODO FIX DATABASE TO MAKE photos.path like '/a/dir' or '/'
      $filename = $photo['filename'];
      $photopath = trim($photo['path'], '/') . '/' . $filename;
      $photopath = rtrim('/'.ltrim($photo['path'],'/'),'/').'/'.$filename;

      // Found in correct location
      if (isset($filesInStoreNotYetMatchedToDB[$photopath])) {
        # Bonus code, if this is local, we can do more verification
        if ($cameralife->GetPref('filestore')=='local' && $photo['fsize']) {
          $photofile = $cameralife->FileStore->PhotoDir."/$photopath";
          $actualsize = filesize($photofile);
          // Found, but changed
          if ($actualsize != $photo['fsize']) {
            $retval[] = "$photopath was changed, flushing cache";
            $photoObj = new Photo($photo['id']);
            $photoObj->Revert();
            $photoObj->LoadImage(true); // TRUE == onlyWantEXIF
            $photoObj->Revert(); // saves $photo->record
            $photoObj->Destroy();
          }
        }
        unset ($filesInStoreNotYetMatchedToDB[$photopath]);
        continue;
      }

      // Look for a photo in the same place, but with the filename capitalization changed
      if (isset($filesInStoreNotYetMatchedToDB[strtolower($photopath)])) {
        unset ($filesInStoreNotYetMatchedToDB[strtolower($photopath)]);
        continue;
      }

      if (isset($filesInStoreNotYetMatchedToDB[strtoupper($photopath)])) {
        unset ($filesInStoreNotYetMatchedToDB[strtoupper($photopath)]);
        continue;
      }

/*
      // Was photo renamed lcase?
      if ($filename != strtolower($filename)) {
        $candidatephotopaths = array_keys($filesInStoreNotYetMatchedToDB, strtolower($filename));
        foreach ($candidatephotopaths as $candidatephotopath) {
          $candidatedirname=dirname($candidatephotopath);
          $candidatefilename=dirname($candidatephotopath);
          if ($candidatedirname) $candidatedirname .= '/';
          if ($candidatedirname == './') $candidatedirname = '';
          if ($photo['path'] == $candidatedirname) {
            unset ($filesInStoreNotYetMatchedToDB[$candidatephotopath]);
            $cameralife->Database->Update('photos',array('filename'=>$candidatefilename),'id='.$photo['id']);
            continue 2;
          }
        }
      }

      // Was photo renamed ucase?
      if ($filename != strtoupper($filename)) {
        $candidatephotopaths = array_keys($filesInStoreNotYetMatchedToDB, strtoupper($filename));
        foreach ($candidatephotopaths as $candidatephotopath) {
          $candidatedirname=dirname($candidatephotopath);
          $candidatefilename=dirname($candidatephotopath);
          if ($candidatedirname) $candidatedirname .= '/';
          if ($candidatedirname == './') $candidatedirname = '';
          if ($photo['path'] == $candidatedirname) {
            unset ($filesInStoreNotYetMatchedToDB[$candidatephotopath]);
            $cameralife->Database->Update('photos',array('filename'=>$candidatefilename),'id='.$photo['id']);
            continue 2;
          }
        }
      }
*/

/*
      // Look for a photo with the same name and filesize anywhere else
      $candidatephotopaths = array_keys($filesInStoreNotYetMatchedToDB, $filename);
      foreach ($candidatephotopaths as $candidatephotopath) {
        $candidatedirname=dirname($candidatephotopath);
//TODO AND CHECK FILESIZE
        if ($candidatedirname) $candidatedirname .= '/';
        if ($candidatedirname == './') $candidatedirname = '';

        $cameralife->Database->Update('photos',array('path'=>$candidatedirname),'id='.$photo['id']);
        $retval[] = "$filename moved to $candidatedirname";
        unset ($filesInStoreNotYetMatchedToDB[$candidatephotopath]);

        # keep track of the number 0234 in like DSCN_0234.jpg
        $number = preg_replace('/[^\d]/','',$filename);
        if ($number > 1000)
          $lastmoved = array($number, $candidatedirname);
        continue 2;
      }
*/

/*
      // If two photos with consecutive names are moved to another directory
      // AND one of them was modified outside of Camera Life
      // then this will find it
      //
      // (otherwise a photo that was moved and changed would be considered lost)
      $lastmoved = NULL;
      foreach ($candidatephotopaths as $candidatephotopath) {
        $number = preg_replace('/[^\d]/','',$candidatephotopath);

        if ($number > 1000 && abs($number - $lastmoved[0])<5 && $newpath == $lastmoved[1]) {
          $candidatedirname=dirname($candidatephotopath).'/';
          if ($candidatedirname) $candidatedirname .= '/';
          if ($candidatedirname=='./') $candidatedirname = '';

          $cameralife->Database->Update('photos',array('path'=>$candidatedirname),'id='.$photo['id']);
          $retval[] = "$photopath probably moved to $candidatedirname";
          unset ($filesInStoreNotYetMatchedToDB[$candidatephotopath]);
          $lastmoved = array($number, $candidatedirname);
          continue 2;
        } else {
          $str = $photo['path'].$photo['filename']." is missing, and $candidatephotopath was found, ";
          $str .= "they are not the same, I don't know what to do... ";
          $str .= "If they are the same, move latter to former, update, then move back.";
          $str .= "If they are different, move latter out of the photo directory, update and then move back.";

          $retval[] = $str;
          unset ($filesInStoreNotYetMatchedToDB[$photopath]);
#          unset ($filesInStoreNotYetMatchedToDB[$candidatephotopath]); # needed?
          continue 2;
        }
      }
*/
      // Photo not found anywhere
      $retval[] = "$photopath was deleted from filesystem";
      $photoObj = new Photo($photo['id']);
var_dump($filesInStoreNotYetMatchedToDB, $photopath);      
      $photoObj->Erase();
    }

    /**
    * $filesInStoreNotYetMatchedToDB now contains a list of existing files that are not in the database
    * Maximum effort will be made to not add these new files to the DB
    */

    foreach ($filesInStoreNotYetMatchedToDB as $new_file => $newbase) {
      if (preg_match("/^picasa.ini|digikam3.db$/i",$newbase))
        continue;
      if (!preg_match("/.jpg$|.jpeg$|.png$|.gif$/i",$newbase)) {
        $retval[] = "Skipped $new_file because it is not a JPEG or PNG file";
        continue;
      }

      $newpath=dirname($new_file);
      $condition = "filename LIKE '".mysql_real_escape_string($newbase)."'";
      $result = $cameralife->Database->Select('photos','id, filename, path',$condition);

      // Is anything in the filestore too similar (given available information) to let this photo in?
      if ($photo = $result->FetchAssoc()) {
        // With the case-insensitive LIKE above, this will handle files renamed only by case
        if (strcasecmp($photo['path'].$photo['filename'], $new_file) == 0) {
          $retval[] = $photo['path'].$photo['filename'].' was renamed to '.$new_file;
          $cameralife->Database->Update('photos',array('filename'=>$newbase),'id='.$photo['id']);
          continue;
        }
        $photoFullpath = rtrim('/'.ltrim($photo['path'],'/'),'/').'/'.$photo['filename'];

        # Bonus code
        $same = FALSE;
        if ($cameralife->GetPref('filestore')=='local') {
          $a = file_get_contents($cameralife->FileStore->PhotoDir . $photoFullpath);
          $b = file_get_contents($cameralife->FileStore->PhotoDir . $new_file);
          if ($a == $b)
            $same = TRUE;
        }

        if ($same)
          $error = 'Two photos in your file store are identical, please delete one: ';
        else
          $error  = 'Two photos in your file store are too similar, please delete one: ';
        $error .= "$photoFullpath is in the system, $new_file is not";
        $retval[] = $error;
        continue;
      }

      $retval[] = "Added $new_file\n";

      $photoObj = new Photo(array('filename'=>$newbase, 'path'=>$newpath));
      $photoObj->Destroy();
    }

    return $retval;
  }

  /**
  * Does a quick compare of Database and FileStore and checks if they are same
  *
  * @return true or false
  */
  public function Fsck()
  {
    global $cameralife;
    $files = $cameralife->FileStore->ListFiles('photo', $this->path, FALSE);
    if(!is_array($files)) return FALSE;

    $fsphotos = $fsdirs = array();
    foreach ($files as $file) {
      if (preg_match("/.jpg$|.jpeg$|.png$|.gif$/i",$file))
        $fsphotos[] = $file;
      else {
        $fsdirs[] = $file;
      }
    }

    $selection = "filename";
    $condition = "path = '".addslashes($this->path)."'";
    $result = $cameralife->Database->Select('photos', $selection, $condition);
    while ($row = $result->FetchAssoc()) {
      $key = array_search($row['filename'], $fsphotos);
      if($key === FALSE)

        return FALSE;
      else
        unset ($fsphotos[$key]);
    }

    $selection = "DISTINCT SUBSTRING_INDEX(SUBSTR(path,".(strlen($this->path)+1)."),'/',1) AS basename";
    $condition = "path LIKE '".addslashes($this->path)."%/' AND status=0";
    $result = $cameralife->Database->Select('photos', $selection, $condition);
    while ($row = $result->FetchAssoc()) {
      $key = array_search($row['basename'], $fsdirs);
      if($key === FALSE)

        return FALSE;
      else
        unset ($fsdirs[$key]);
    }

    return (count($fsphotos) + count($fsdirs) == 0);
  }
}
