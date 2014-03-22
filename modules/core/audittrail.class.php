<?php
/**
 * The class for logging and reverting changes to the site
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

class AuditTrail
{
  public function AuditTrail()
  {

  }

  /**
   * Logs information about a user and a change to the database, so this can be undone later
   *
   * @param string $record_type one of ('photo','album','preference','user')
   * @param int $record_id id of the record being changed
   * @param string $value_field field being changed
   * @param string $value_old old field value
   * @param string $value_new new field value
   */
  public function Log($record_type, $record_id, $value_field, $value_old, $value_new)
  {
    global $user, $_SERVER, $cameralife;
    if ($value_old==$value_new) return;
    $log['record_type'] = $record_type;
    $log['record_id'] = $record_id;
    $log['value_field'] = $value_field;
    $log['value_new'] = $value_new;
    $log['user_name'] = $cameralife->Security->GetName();
    $log['user_ip'] = $_SERVER['REMOTE_ADDR'];
    $log['user_date'] = date('Y-m-d');
    $id = $cameralife->Database->Insert('logs',$log);

    return new Receipt($id);
  }

  /**
   * Revert Camera Life to the state before the specified action took effect
   *
   * This also removes said action from the logs.
   * @param int $id is the ID of the receipt representing action to revert
   */
  public function Undo($id)
  {
    global $cameralife;
    if (!is_numeric($id))
      $cameralife->Error('Invalid receipt.');
    $result = $cameralife->Database->Select('logs', '*', 'id='.$id);
    $receipt = $result->FetchAssoc();
    $condition = 'record_id='.$receipt['record_id'];
    $condition .= " AND record_type='".$receipt['record_type']."'";
    $condition .= " AND value_field='".$receipt['value_field']."'";
    $condition .= " AND id <= ".$id;
    # Gets the requested AND previous log entry for a record and value field
    $result = $cameralife->Database->Select('logs', '*', $condition, 'ORDER BY id DESC LIMIT 2');

    $target = $result->FetchAssoc();
    $prior = $result->FetchAssoc();
    if (is_array($prior) && isset($prior['value_new'])) {
      $oldvalue = $prior['value_new'];
    } else {
      switch ($receipt['record_type'].'_'.$receipt['value_field']) {
        case 'photo_description':
          $oldvalue = 'unnamed';
          break;
        case 'photo_status':
          $oldvalue = '0';
          break;
        case 'photo_keywords':
          $oldvalue = '';
          break;
        case 'photo_flag':
          $oldvalue = '';
          break;
        case 'album_name':
          $oldvalue = '';
          break;
        case 'photo_poster_id':
          $album = new Album($record['record_id']);
          $condition = "status=0 and lower(description) like lower('%".$album->Get['term']."%')";
          $query = $cameralife->Database->Select('photos','id',$condition);
          $result = $query->FetchAssoc();
          if ($result)
            $oldvalue = $result['id'];
          else
            $cameralife->Error("Cannot find a poster for the album #".$record['record_id'], __FILE__, __LINE__);
          break;
        default:
          $cameralife->Error("I don't know how to undo the parameter ".$receipt['record_type'].'_'.$receipt['value_field'], __FILE__, __LINE__);
      }
    }

    $mod = array($receipt['value_field']=>$oldvalue);
    $cameralife->Database->Update($receipt['record_type'].'s', $mod, 'id='.$receipt['record_id']);

    $condition = 'record_id='.$receipt['record_id'];
    $condition .= " AND record_type='".$receipt['record_type']."'";
    $condition .= " AND value_field='".$receipt['value_field']."'";
    $condition .= " AND id >= ".$id;
    $cameralife->Database->Delete('logs', $condition);
  }
}

/**
 * This is what you get when you effect a change on Camera Life
 */
class Receipt
{
  public $myRecord;

  /// Retrieves a recepit from the database. Receipt records are created by Log().
  public function Receipt($id)
  {
    global $cameralife;

    if (!is_numeric($id))
      $cameralife->Error("Invalid receipt id", __FILE__, __LINE__);
    $result = $cameralife->Database->Select('logs', '*', 'id='.$id);
    $this->myRecord = $result->FetchAssoc();
    if (!is_array($this->myRecord))
      $cameralife->Error("Invalid receipt id #$id", __FILE__, __LINE__);
  }

  /// Returns true if this receipt represents the most recent change to the affected record
  public function IsValid()
  {
    global $cameralife;

    $condition = 'record_id='.$this->myRecord['record_id'];
    $condition .= " AND record_type='".$this->myRecord['record_type']."'";
    $condition .= " AND value_field='".$this->myRecord['value_field']."'";
    $result = $cameralife->Database->Select('logs', '*', $condition, 'ORDER BY id DESC LIMIT 1');

    $new = $result->FetchAssoc();

    return ($new['id'] == $this->myRecord['id']);
  }

  public function Get($item)
  {
    return $this->myRecord[$item];
  }

  public function GetDescription()
  {
    if ($this->myRecord['record_type']=='photo' && $this->myRecord['value_field'] == 'description')
      return 'The description has been updated.';
    if ($this->myRecord['record_type']=='photo' && $this->myRecord['value_field'] == 'status')
      return 'The photo has been flagged.';
    return 'Action completed.';
  }

  public function GetObject()
  {
    if ($this->myRecord['record_type']=='photo')
      return new Photo($this->myRecord['record_id']);
    if ($this->myRecord['record_type']=='album')
      return new Album($this->myRecord['record_id']);
    if ($this->myRecord['record_type']=='preference')
      return $cameralife;
    if ($this->myRecord['record_type']=='user')
      return die("user receipt type"); // wtf do I do here?
    $cameralife->Error("Invalid receipt type.");
  }

  // Returns all receipts from this back to the beginning
  public function GetChain($checkpoint=-1)
  {
    global $cameralife;
    $retval = array();
    $condition = "value_field='".$this->myRecord['value_field']."'";
    $condition .= " AND record_type='".$this->myRecord['record_type']."'";
    $condition .= " AND record_id='".$this->myRecord['record_id']."'";
    $condition .= " AND id>$checkpoint";
    $query = $cameralife->Database->Select('logs', 'id', $condition, 'ORDER BY id');
    while ($row = $query->FetchAssoc()) {
      $retval[] = new Receipt($row['id']);
    }

    return $retval;
  }

  // Finds the previous record value
  // returns: {value:OLDVALUE,fromReceipt:TRUE|FALSE}
  public function GetOld()
  {
    global $cameralife;

    $condition = 'record_id='.$this->myRecord['record_id'];
    $condition .= " AND record_type='".$this->myRecord['record_type']."'";
    $condition .= " AND value_field='".$this->myRecord['value_field']."'";
    $condition .= " AND id <= ".$this->myRecord['id'];
    # Gets the requested AND previous log entry for a record and value field
    $result = $cameralife->Database->Select('logs', '*', $condition, 'ORDER BY id DESC LIMIT 2');

    $target = $result->FetchAssoc();
    $prior = $result->FetchAssoc();
    if (is_array($prior) && isset($prior['value_new'])) {
      return array('value'=>$prior['value_new'], 'fromReceipt'=>TRUE);
    } else {
      switch ($this->myRecord['record_type'].'_'.$this->myRecord['value_field']) {
        case 'photo_description':
          return array('value'=>'unnamed', 'fromReceipt'=>FALSE);
        case 'photo_status':
          return array('value'=>0, 'fromReceipt'=>FALSE);
        case 'photo_keywords':
          return array('value'=>'', 'fromReceipt'=>FALSE);
        case 'photo_flag':
          return array('value'=>'', 'fromReceipt'=>FALSE);
        case 'album_name':
          return array('value'=>'', 'fromReceipt'=>FALSE);
        case 'album_poster_id':
          $album = new Album($this->myRecord['record_id']);
          $condition = "status=0 and lower(description) like lower('%".$album->Get['term']."%')";
          $query = $cameralife->Database->Select('photos','id',$condition);
          $result = $query->FetchAssoc();
          if ($result)
            return array('value'=>$result['id'], 'fromReceipt'=>FALSE);
          else
            $cameralife->Error("Cannot find a poster for the album #".$this->myRecord['record_id'], __FILE__, __LINE__);
          break;
        default:
          $cameralife->Error("I don't know how to undo the parameter ".$this->myRecord['record_type'].'_'.$this->myRecord['value_field'], __FILE__, __LINE__);
      }
    }
  }
}
