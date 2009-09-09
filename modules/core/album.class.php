<?php
  # the class for getting and using albums
/**
*Enables you to get albums
*@link http://fdcl.sourceforge.net
  *@version 2.6.3b3
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */
  /**
  *For getting and using albums
  */

class Album extends Search
{
  var $record;
  /**
  *
  *<code>is_array($orginal)</code> will be a new album given in parts
  *<code>is_numeric($original)</code> is an ID
  *
  *@param mixed $original A unique ID
  */
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
    elseif(is_numeric($original))  # This is an ID
    {
      $result = $cameralife->Database->Select('albums', '*', "id=$original");
      $this->record = $result->FetchAssoc()
        or die('Bad album :-(');
    }
    else
    {
      $cameralife->Error("Invalid album", __FILE__, __LINE__);
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

  function SetPoster($poster)
  {
    global $cameralife;

    if (!is_numeric($poster))
      $cameralife->Error("Failed to set poster for album", __FILE__, __LINE__);

    $cameralife->Database->SelectOne('photos','COUNT(*)','status=1 AND id='.$_GET['poster_id'])
      or $cameralife->Error('The selected poster photo does not exist', __FILE__, __LINE__);

    $this->Set('poster_id', $_GET['poster_id']);
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
  /** @param string $size size of the image (large)
  */

  function GetIcon($size='large')
  {
    global $cameralife;

    $retval = array();

    if ($cameralife->GetPref('rewrite') == 'yes')
      $retval['href'] = $cameralife->base_url.'/albums/'.$this->record['id'];
    else
      $retval['href'] = $cameralife->base_url.'/album.php&#63;id='.$this->record['id'];

    if ($size == 'large')
    {
      $photo = new Photo($this->record['poster_id']);
      $retval['image'] = $photo->GetMedia('thumbnail');
    }
    else
    {
      $retval['image'] = 'small-album';
    }

    $retval['name'] = $this->record['name'];
    $retval['context'] = $this->record['hits'];
    $retval['rel'] = 'tag';

    return $retval;
  }
}
?>
