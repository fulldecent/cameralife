<?php

    /**
    *for the homepage visit @link  http://fdcl.sourceforge.net/
    *@version 2.6.3b5
    *@author Will Entriken <WillEntriken @gmail.com>
    *@access public
    *@copyright Copyright (c) 2001-2009 Will Entriken
    */
     /**
      *This class is for getting and using topics
      */
class Topic extends Search
{
  var $name;

  function Topic($name)
  {
    global $cameralife;

    $this->name = $name;

    Search::Search('');
    $this->mySearchAlbumCondition = "topic = '".mysql_real_escape_string($this->name)."'";
    $this->mySearchPhotoCondition = "FALSE";
    $this->mySearchFolderCondition = "FALSE";
  }

  function GetName()
  {
    return htmlentities($this->name);
  }

  function Get($item)
  {
    return $this->$item;
  }

  function GetIcon($size='large')
  {
    global $cameralife;
    $retval = array();

    if ($cameralife->GetPref('rewrite') == 'yes')
      $retval['href'] = $cameralife->base_url.'/topics/'.htmlentities($this->name);
    else
      $retval['href'] = $cameralife->base_url.'/topic.php&#63;name='.htmlentities($this->name);

    $retval['name'] = htmlentities($this->name);

    if ($size=='large')
      $retval['image'] = $cameralife->IconURL('topic');
    else
      $retval['image'] = $cameralife->IconURL('small-topic');

    return $retval;
  }

  # STATIC
  /**
  *This function is a static function
  */
  function GetTopics()
  {
    global $cameralife;

    $retval = array();
    $result = $cameralife->Database->Select('albums','DISTINCT topic');
    while ($topic = $result->FetchAssoc())
      $retval[] = $topic['topic'];

    return $retval;
  }


}

?>
