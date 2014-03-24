<?php
/**
 * Model class for albums
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */
class Album extends Search
{
  public $record;
  /**
   *<code>is_array($orginal)</code> will be a new album given in parts
   *<code>is_numeric($original)</code> is an ID
   *
   * @param mixed $original A unique ID
   */
  public function __construct($original)
  {
    global $cameralife;

    if (is_array($original)) { # A new album, given by parts
      $search = new Search($original['term']);
      $count = $search->getCounts();
      if ($count['photos'] == 0)
        die ("error making album, no photos with term");
      $result = $search->getPhotos();

      $this->record['topic'] = $original['topic'];
      $this->record['name'] = $original['name'];
      $this->record['term'] = $original['term'];
      $this->record['poster_id'] = $result[0]->Get('id');
      $this->record['id'] = $cameralife->database->Insert('albums', $this->record);
    } elseif (is_numeric($original)) {  # This is an ID
      $result = $cameralife->database->Select('albums', '*', "id=$original");
      $this->record = $result->FetchAssoc();
      if (!$this->record) {
        header("HTTP/1.0 404 Not Found");
        $cameralife->error("Album #".($original+0)." not found.");
      }
    } else {
      $cameralife->error("Invalid album", __FILE__, __LINE__);
    }
    parent::__construct($this->record['term']);
  }

  public function set($key, $value)
  {
    global $cameralife;

    $receipt = NULL;
    if ($key != 'hits')
      $receipt = AuditTrail::log('album',$this->record['id'],$key,$this->record[$key],$value);
    $this->record[$key] = $value;
    $cameralife->database->Update('albums', array($key=>$value), 'id='.$this->record['id']);

    return $receipt;
  }

  public function get($key)
  {
    return $this->record[$key];
  }

  public function getPoster()
  {
    if (Photo::photoExists($this->record['poster_id']))
      return new Photo($this->record['poster_id']);
    else {
      $photos = $this->getPhotos();

      return $photos[0];
    }

  }

  public function setPoster($poster)
  {
    global $cameralife;

    if (!is_numeric($poster))
      $cameralife->error("Failed to set poster for album", __FILE__, __LINE__);

    $cameralife->database->SelectOne('photos','COUNT(*)','status=1 AND id='.$_GET['poster_id'])
      or $cameralife->error('The selected poster photo does not exist', __FILE__, __LINE__);

    $this->set('poster_id', $_GET['poster_id']);
  }

  public function getTopic()
  {
    return new Topic($this->record['topic']);
  }

  public function erase()
  {
    global $cameralife;

    $cameralife->database->Delete('albums','id='.$this->record['id']);
    $cameralife->database->Delete('logs',"record_type='album' AND record_id=".$this->record['id']);
  }

  public function getOpenGraph()
  {
    global $cameralife;
    $retval = array();
    $retval['og:title'] = $this->record['name'];
    $retval['og:type'] = 'website';
    $retval['og:url'] = $cameralife->baseURL.'/albums/'.$this->record['id'];
    if ($cameralife->getPref('rewrite') == 'no')
      $retval['og:url'] = $cameralife->baseURL.'/album.php?id='.$this->record['id'];
    $photo = $this->getPoster();
    $retval['og:image'] = $photo->getMedia('thumbnail');
    $retval['og:image:type'] = 'image/jpeg';
    //$retval['og:image:width'] =
    //$retval['og:image:height'] =
    return $retval;
  }
}
