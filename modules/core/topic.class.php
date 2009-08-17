<?php

    /**
    *for the homepage visit @link  http://fdcl.sourceforge.net/
    *@version 2.6.2
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

    if ($cameralife->GetPref('rewrite') == 'yes')
      $href = $cameralife->base_url.'/topics/'.htmlentities($this->name);
    else
      $href = $cameralife->base_url.'/topic.php&#63;name='.htmlentities($this->name);

    return array('href'=>$href,
                 'name'=>htmlentities($this->name),
                 'image'=>($size=='large')?'topic':'small-topic');
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
