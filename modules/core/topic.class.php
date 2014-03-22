<?php
/**
 * Topic class.
 * 
 * @author Will Entriken <WillEntriken @gmail.com>
 * @access public
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @extends Search
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

//TODO DEPRECATED?
  public function GetName()
  {
    return htmlentities($this->name);
  }

  public function Get($item)
  {
    return $this->$item;
  }

  public static function GetTopics()
  {
    global $cameralife;
    $retval = array();
    $result = $cameralife->Database->Select('albums','DISTINCT topic');
    while ($topic = $result->FetchAssoc())
      $retval[] = new Topic($topic['topic']);
    return $retval;
  }

  public function GetOpenGraph()
  {
    global $cameralife;
    $retval = array();
    $retval['og:title'] = $this->name;
    $retval['og:type'] = 'website';
    $retval['og:url'] = $cameralife->baseURL.'/topics/'.rawurlencode($this->name);
    if ($cameralife->GetPref('rewrite') == 'no')
      $retval['og:url'] = $cameralife->baseURL.'/topic.php?name='.rawurlencode($this->name);
    $retval['og:image'] = $cameralife->IconURL('topic');
    $retval['og:image:type'] = 'image/png';
    //$retval['og:image:width'] = 
    //$retval['og:image:height'] = 
    return $retval;    
  }
}
