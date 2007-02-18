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
}

?>
