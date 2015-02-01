<?php
namespace CameraLife\Models;

/**
 * The class for logging and reverting changes to the site
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
 * @access public
 */
class AuditTrail extends IndexedModel
{
    public $record;

    /**
     * Logs information about a user and a change to the database, so this can be undone later
     *
     * @param  string $record_type one of ('photo','album','preference','user')
     * @param  int    $record_id   id of the record being changed
     * @param  string $value_field field being changed
     * @param  string $value_old   old field value
     * @param  string $value_new   new field value
     * @return Audit trail of the action performed
     */
    public static function createAuditTrailForChange($record_type, $record_id, $value_field, $value_old, $value_new)
    {
        global $_SERVER, $cameralife;
        if ($value_old == $value_new) {
            return null;
        }
        $retval = new AuditTrail();
        $retval->record['record_type'] = $record_type;
        $retval->record['record_id'] = $record_id;
        $retval->record['value_field'] = $value_field;
        $retval->record['value_new'] = $value_new;
        $retval->record['user_name'] = $cameralife->security->getName();
        $retval->record['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $retval->record['user_date'] = date('Y-m-d');
        $retval->record['id']  = $cameralife->database->Insert('logs', $retval->record);
        $retval->id = $retval->record['id'];
        return $retval;
    }

    /**
     * getAuditTrailWithID function.
     *
     * @access public
     * @static
     * @param mixed $id
     * @return void
     */
    public static function getAuditTrailWithID($id)
    {
        $retval = new AuditTrail;
        $query = Database::select('logs', '*', 'id=' . $id);
        $retval->record = $query->fetchAssoc()
            or throw new \Exception('AT not found');
        $retval->id = $retval->record['id'];
        if (!is_array($retval->record)) {
            $cameralife->error("Invalid receipt id #$id");
        }
        return $retval;
    }

    /**
     * Get previous value for this record field
     *
     * @access public
     * @return mixed
     */
    public function previousValue()
    {
        $condition = 'record_id=' . $this->record['record_id'];
        $condition .= " AND record_type='" . $this->record['record_type'] . "'";
        $condition .= " AND value_field='" . $this->record['value_field'] . "'";
        $condition .= " AND id < " . $this->record['id'];
        $result = Database::select('logs', '*', $condition, 'ORDER BY id DESC LIMIT 1');
        $prior = $result->fetchAssoc();
        if (is_array($prior) && isset($prior['value_new'])) {
            $old = $prior['value_new'];
        } else {
            $old = AuditTrail::getDefaultForRecordTypeAndValueField($this->record['record_type'], $this->record['value_field']);
        }
        return $old;
    }

    /**
     * Revert Camera Life to the state before the specified action took effect
     *
     * @access public
     * @return void
     */
    public function revertChange()
    {
        $previousValue = $this->previousValue();
        $mod = array($this->record['value_field'] => $old);
        Database::update($this->record['record_type'] . 's', $mod, 'id=' . $this->record['record_id']);
        $condition = 'record_id=' . $this->record['record_id'];
        $condition .= " AND record_type='" . $this->record['record_type'] . "'";
        $condition .= " AND value_field='" . $this->record['value_field'] . "'";
        $condition .= " AND id >= " . $id;
        Database::delete('logs', $condition);
    }

    /**
     * isValid function.
     *
     * @access public
     * @return bool true if this receipt represents the most recent change to the affected record
     */
    public function isValid()
    {
        $condition = 'record_id=' . $this->record['record_id'];
        $condition .= " AND record_type='" . $this->record['record_type'] . "'";
        $condition .= " AND value_field='" . $this->record['value_field'] . "'";
        $result = Database::select('logs', '*', $condition, 'ORDER BY id DESC LIMIT 1');
        $newest = $result->fetchAssoc();
        return ($newest['id'] == $this->record['id']);
    }

    /**
     * Getter for elements in my record
     *
     * @access public
     * @param mixed $item
     * @return mixed
     */
    public function get($item)
    {
        return $this->record[$item];
    }

    /**
     * English language description for the action performed
     *
     * @access public
     * @return void
     */
    public function getDescription()
    {
        if ($this->record['record_type'] == 'photo' && $this->record['value_field'] == 'description') {
            return 'The description has been updated.';
        }
        if ($this->record['record_type'] == 'photo' && $this->record['value_field'] == 'status') {
            return 'The photo has been flagged.';
        }
        return 'Action completed.';
    }

    /**
     * Factory for the associated object
     *
     * @access public
     * @return mixed
     */
    public function getObject()
    {
        if ($this->record['record_type'] == 'photo') {
            return Photo::getPhotoWithID($this->record['record_id']);
        }
        if ($this->record['record_type'] == 'album') {
            return new Album($this->record['record_id']);
        }
        if ($this->record['record_type'] == 'preference') {
            return $cameralife;
        }
        $cameralife->Error("Unknown receipt type: " . $this->record['record_type']);
        return false;
    }

    /**
     * Returns all receipts from this back to the beginning
     *
     * @access public
     * @param float $checkpoint (default: -1)
     * @return void
     */
    public function getTrailsBackToCheckpoint($checkpoint = -1)
    {
        $retval = array();
        $condition = "value_field='" . $this->record['value_field'] . "'";
        $condition .= " AND record_type='" . $this->record['record_type'] . "'";
        $condition .= " AND record_id='" . $this->record['record_id'] . "'";
        $condition .= " AND id>$checkpoint";
        $query = Database::select('logs', 'id', $condition, 'ORDER BY id');
        while ($row = $query->fetchAssoc()) {
            $retval[] = AuditTrail::getAuditTrailWithID($row['id']);
        }
        return $retval;
    }

    /**
     * Gets the default value for a record type and field
     *
     * @access public
     * @static
     * @param mixed $recordType
     * @param mixed $valueField
     * @return mixed
     */
    private static function getDefaultForRecordTypeAndValueField($recordType, $valueField)
    {
        switch ($recordType . '_' . $valueField) {
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
                $condition = "status=0 and lower(description) like lower('%" . $album->Get['term'] . "%')";
                $query = Database::select('photos', 'id', $condition);
                $result = $query->fetchAssoc();
                if ($result) {
                    $oldvalue = $result['id'];
                } else {
                    $cameralife->error("Cannot find a poster for the album #" . $record['record_id']);
                }
                break;
            default:
                throw new \Exception("I don't know default value for -$recordType- -$valueField-");
        }
        return $oldvalue;
    }
}
