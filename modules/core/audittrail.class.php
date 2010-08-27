<?php
  # The class for logging and reverting changes to the site
  /**Logging and reverting changes to the site
  *
  *@version 2.6.3b5
    *@author Will Entriken <cameralife@phor.net>
    *@copyright Copyright (c) 2001-2009 Will Entriken
    *@access public
  *@link http://fdcl.sourceforge.net
  */
  /**
  *<b>For editing the site</b>
  *The class enables you to
  *<ul><li>Make changes to the site</li><li>Undo the changes</li>
  *</ul>

  */

class AuditTrail
{
  function AuditTrail()
  {

  }


/**
 *
 *
 *  db_log - Logs changes to the database. Information about the user is saved with information below This allows changes to be rolled back later.
 *
 * @param string $record_type one of ('photo','album','preference','user')
 * @param int $record_id id of the record being changed
 * @param string $value_field field being changed
 * @param string $value_old old field value
 * @param string $value_new new field value
 */




  function Log($record_type, $record_id, $value_field, $value_old, $value_new)
  {
    global $user, $_SERVER, $cameralife;
    if ($value_old==$value_new) return;

    $log['record_type'] = $record_type;
    $log['record_id'] = $record_id;
    $log['value_field'] = $value_field;
    $log['value_new'] = $value_new;
    $log['value_old'] = $value_old;
    $log['user_name'] = $cameralife->Security->GetName();
    $log['user_ip'] = $_SERVER['REMOTE_ADDR'];
    $log['user_date'] = date('Y-m-d');

    $id = $cameralife->Database->Insert('logs',$log);
    return new Receipt($id);
  }
}
/**
*<b>Acknowledges the changes </b>
*/

class Receipt
{
  var $myRecord;

  function Receipt($id = NULL)
  {
    global $cameralife;

    if(is_numeric($id))
    {
      $result = $cameralife->Database->Select('logs', '*', 'id='.$id);
      $this->myRecord = $result->FetchAssoc();
    }
  }

  function IsValid()
  {
    global $cameralife;

    if ($this->myRecord == FALSE)
      return FALSE;

    $condition = 'record_id='.$this->myRecord['record_id'];
    $condition .= " AND record_type='".$this->myRecord['record_type']."'";
    $condition .= " AND value_field='".$this->myRecord['value_field']."'";
    $result = $cameralife->Database->Select('logs', '*', $condition, 'ORDER BY id DESC LIMIT 1');

    $new = $result->FetchAssoc();
    return ($new['id'] == $this->myRecord['id']);
  }

  function GetDescription()
  {
    if (!is_array($this->myRecord))
      return 'Invalid receipt.';
    if ($this->myRecord['record_type']=='photo' && $this->myRecord['value_field'] == 'description')
      return 'The description has been updated.';
    if ($this->myRecord['record_type']=='photo' && $this->myRecord['value_field'] == 'status')
      return 'The photo has been flagged.';
    return 'Action completed.';
  }

  function Get($item)
  {
    return $this->myRecord[$item];
  }

  // invalidates this receipt
  /**
  *Invalidates this receipt
  *
  *Gets the old records
  *<code> $result = $cameralife->Database->Select('logs', '*', $condition, 'ORDER BY id DESC LIMIT 2');</code>
  */
  function Undo()
  {
    global $cameralife;

    if (!is_array($this->myRecord))
      $cameralife->Error('Invalid receipt.');

    $condition = 'record_id='.$this->myRecord['record_id'];
    $condition .= " AND record_type='".$this->myRecord['record_type']."'";
    $condition .= " AND value_field='".$this->myRecord['value_field']."'";
    # Gets the old one too...
    $result = $cameralife->Database->Select('logs', '*', $condition, 'ORDER BY id DESC LIMIT 2');

    $new = $result->FetchAssoc();
    if ($new['id'] != $this->myRecord['id'])
    {
      $cameralife->Error('This is not the latest change made, so it cannot be undone.', __FILE__, __LINE__);
      return FALSE;
    }
    if ($new['user_name'] != $cameralife->Security->GetName())
    {
      $cameralife->Error('You did not make this change, so you cannot undo it.', __FILE__, __LINE__);
      return FALSE;
    }

    $old = $result->FetchAssoc();
    if(is_array($old) && isset($old['value_new']))
    {
      $oldvalue = $old['value_new'];
    }
    else
    {
      switch ($this->myRecord['record_type'].'_'.$this->myRecord['value_field'])
      {
        case 'photo_description':
          $oldvalue = 'unnamed';
          break;
        case 'photo_status':
          $oldvalue = '0';
          break;
        default:
          $cameralife->Error('I don\t know how to undo that.');
      }
    }

    $mod = array($this->myRecord['value_field']=>$oldvalue);
    $cameralife->Database->Update($this->myRecord['record_type'].'s', $mod, 'id='.$this->myRecord['record_id']);
    $cameralife->Database->Delete('logs', 'id='.$this->myRecord['id']);
    unset($myRecord);
  }
}

?>
