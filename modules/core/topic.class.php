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

  function Get($item)
  {
    return $this->$item;
  }

  function GetIcon($size='large')
  {
    global $cameralife;

    if ($cameralife->GetPref('rewrite') == 'yes')
      $href = $cameralife->base_url.'/topics/'.$this->name;
    else
      $href = $cameralife->base_url.'/topic.php?name='.$this->name;

    return array('href'=>$href,
                 'name'=>$this->name,
                 'image'=>($size=='large')?'topic':'small-topic');
  }
}

?>
