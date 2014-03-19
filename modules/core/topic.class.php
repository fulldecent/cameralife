<?php

    /**
    *@author Will Entriken <WillEntriken @gmail.com>
    *@access public
    *@copyright Copyright (c) 2001-2009 Will Entriken
    */
     /**
      *This class is for getting and using topics
      */
class Topic extends Search
{
  public $name;

  public function Topic($name)
  {
    global $cameralife;

    $this->name = $name;

    Search::Search('');
    $this->mySearchAlbumCondition = "topic = '".mysql_real_escape_string($this->name)."'";
    $this->mySearchPhotoCondition = "FALSE";
    $this->mySearchFolderCondition = "FALSE";
  }

  public function GetName()
  {
    return htmlentities($this->name);
  }

  public function Get($item)
  {
    return $this->$item;
  }

  public function GetIcon($size='large')
  {
    global $cameralife;
    $retval = array();

    if ($cameralife->GetPref('rewrite') == 'yes')
      $retval['href'] = $cameralife->base_url.'/topics/'.urlencode($this->name);
    else
      $retval['href'] = $cameralife->base_url.'/topic.php&#63;name='.urlencode($this->name);

    $retval['name'] = htmlentities($this->name);

    if ($size=='large')
      $retval['image'] = $cameralife->IconURL('topic');
    else
      $retval['image'] = $cameralife->IconURL('small-topic');

    return $retval;
  }

  public static function GetTopics()
  {
    global $cameralife;
    $retval = array();
    $result = $cameralife->Database->Select('albums','DISTINCT topic');
    while ($topic = $result->FetchAssoc())
      $retval[] = $topic['topic'];
    return $retval;
  }

}
