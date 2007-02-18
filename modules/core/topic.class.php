<?php
  # the class for getting and using topics
  
class Topic extends Search
{
  var $name;

  function Topic($name)
  {
    global $cameralife;

    $this->name = $name;

    Search::Search('');
    $this->mySearchAlbumCondition = "topic = '".$this->name."'";
  }

  function Set($key, $value)
  {
    global $cameralife;

    db_log('album',$record['id'],$key,$this->record[$key],$value);
    $this->record[$key] = $value;
    $cameralife->Database->Update('albums', array($key=>$value), 'id='.$this->record['id']);
  }

  function Get($key)
  {
    return $this->record[$key];
  }

  function GetSmallIcon()
  {
    return array('href'=>'topic.php&#63;name='.$this->name, 
                 'name'=>$this->name,
                 'image'=>'small-topic');
  }
}

?>
