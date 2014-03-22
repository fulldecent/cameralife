<?php
  # the class for getting and using albums
/**
*Enables you to get albums
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */
  /**
  *For getting and using albums
  */

class Album extends Search
{
  public $record;
  /**
  *
  *<code>is_array($orginal)</code> will be a new album given in parts
  *<code>is_numeric($original)</code> is an ID
  *
  *@param mixed $original A unique ID
  */
  public function Album($original)
  {
    global $cameralife;

    if (is_array($original)) { # A new album, given by parts
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
    } elseif (is_numeric($original)) {  # This is an ID
      $result = $cameralife->Database->Select('albums', '*', "id=$original");
      $this->record = $result->FetchAssoc();
      if (!$this->record) {
        header("HTTP/1.0 404 Not Found");
        $cameralife->Error("Album #".($original+0)." not found.");
      }
    } else {
      $cameralife->Error("Invalid album", __FILE__, __LINE__);
    }

    Search::Search($this->record['term']);
  }

  public function Set($key, $value)
  {
    global $cameralife;

    $receipt = NULL;
    if ($key != 'hits')
      $receipt = AuditTrail::Log('album',$this->record['id'],$key,$this->record[$key],$value);
    $this->record[$key] = $value;
    $cameralife->Database->Update('albums', array($key=>$value), 'id='.$this->record['id']);

    return $receipt;
  }

  public function Get($key)
  {
    return $this->record[$key];
  }

  public function GetPoster()
  {
    if (Photo::PhotoExists($this->record['poster_id']))
      return new Photo($this->record['poster_id']);
    else {
      $photos = $this->GetPhotos();

      return $photos[0];
    }

  }

  public function SetPoster($poster)
  {
    global $cameralife;

    if (!is_numeric($poster))
      $cameralife->Error("Failed to set poster for album", __FILE__, __LINE__);

    $cameralife->Database->SelectOne('photos','COUNT(*)','status=1 AND id='.$_GET['poster_id'])
      or $cameralife->Error('The selected poster photo does not exist', __FILE__, __LINE__);

    $this->Set('poster_id', $_GET['poster_id']);
  }

  public function GetTopic()
  {
    return new Topic($this->record['topic']);
  }

  public function Erase()
  {
    global $cameralife;

    $cameralife->Database->Delete('albums','id='.$this->record['id']);
    $cameralife->Database->Delete('logs',"record_type='album' AND record_id=".$this->record['id']);
  }

  public function GetOpenGraph()
  {
    global $cameralife;
    $retval = array();
    $retval['og:title'] = $this->record['name'];
    $retval['og:type'] = 'website';
    $retval['og:url'] = $cameralife->baseURL.'/albums/'.$this->record['id'];
    if ($cameralife->GetPref('rewrite') == 'no')
      $retval['og:url'] = $cameralife->baseURL.'/album.php?id='.$this->record['id'];
    $photo = $this->GetPoster();
    $retval['og:image'] = $photo->GetMedia('thumbnail');
    $retval['og:image:type'] = 'image/jpeg';
    //$retval['og:image:width'] =
    //$retval['og:image:height'] =
    return $retval;
  }
}
