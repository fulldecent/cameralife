<?php
  # the class for getting and using albums
  
class Album extends Search
{
  var $record;

  function Album($original)
  {
    global $cameralife;

    if(is_array($original)) # A new album, given by parts 
    {
      $search = new Search($original['term']);
      $count = $search->GetCounts();
      if ($count['photos'] == 0)
        die ("Error making album, no photos with term");
      $result = $search->GetPhotos();

      $this->record['topic'] = $original['topic'];
      $this->record['name'] = $original['name'];
      $this->record['term'] = $original['term'];
      $this->record['poster_id'] = $result[0]->Get('id');
      $this->record['id'] = $cameralife->Database->Insert('albums', $this->record);
    }
    else  # This is an ID
    {
      $result = $cameralife->Database->Select('albums', '*', "id=$original");
      $this->record = $result->FetchAssoc()
        or die('Bad album :-(');
    }

    Search::Search($this->record['term']);
  }

  function Set($key, $value)
  {
    global $cameralife;

    $receipt = NULL;
    if ($key != 'hits')
      $receipt = AuditTrail::Log('album',$this->record['id'],$key,$this->record[$key],$value);
    $this->record[$key] = $value;
    $cameralife->Database->Update('albums', array($key=>$value), 'id='.$this->record['id']);
    return $receipt;
  }

  function Get($key)
  {
    return $this->record[$key];
  }

  function GetPoster()
  {
    return new Photo($this->record['poster_id']);
  }

  function GetTopic()
  {
    return new Topic($this->record['topic']);
  }

  function Erase()
  {
    global $cameralife;

    $cameralife->Database->Delete('albums','id='.$this->record['id']);
    $cameralife->Database->Delete('logs',"record_type='album' AND record_id=".$this->record['id']);
  }

  function GetIcon()
  {
    return array('href'=>'album.php&#63;id='.$this->record['id'],
                 'name'=>$this->record['name'],
                 'image'=>'media.php&#63;format=thumbnail&amp;id='.$this->record['poster_id'].'&amp;ver=',$this->record['mtime'],
                 'context'=>$this->record['hits'],
                 'rel'=>'tag');
  }

  function GetSmallIcon()
  {
    return array('href'=>'album.php&#63;id='.$this->record['id'],
                 'name'=>$this->record['name'],
                 'image'=>'small-album',
                 'rel'=>'tag');
  }
}
?>
