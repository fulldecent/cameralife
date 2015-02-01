<?php
namespace CameraLife\Models;

/**
 * Model class for tags
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class Tag extends Search
{
    public $record;

    /**
     * __construct function.
     *
     * @access public
     * @param  int $id
     * @return void
     */
    public function __construct($id)
    {
        $result = Database::select('albums', '*', "id=$id");
        $this->record = $result->fetchAssoc();
        if (!$this->record) {
            header("HTTP/1.0 404 Not Found");
            throw new \Exception("Album #" . ($original + 0) . " not found.");
        }
        parent::__construct($this->record['term']);
    }

    public function set($key, $value, User $user = NULL)
    {
        $receipt = null;
        $this->record[$key] = $value;
        Database::update('albums', array($key => $value), 'id=' . $this->record['id']);
        if (isset($user)) {
            $receipt = AuditTrail::createAuditTrailForChange($user, 'album', $this->record['id'], $key, $this->record[$key], $value);
        }
        return $receipt;
    }

    public function get($key)
    {
        return $this->record[$key];
    }

    public function getPoster()
    {
        if (Photo::photoExists($this->record['poster_id'])) {
            return Photo::getPhotoWithID($this->record['poster_id']);
        } else {
            $photos = $this->getPhotos();

            return $photos[0];
        }

    }

    public function setPoster($poster)
    {
        if (!is_numeric($poster)) {
            throw new \Exception("Failed to set poster for tags");
        }

        $cameralife->database->SelectOne('photos', 'COUNT(*)', 'status=1 AND id=' . $poster)
        or $cameralife->error('The selected poster photo does not exist');

        $this->set('poster_id', $poster);
    }

    public function getTagCollection()
    {
        return new TagCollection($this->record['topic']);
    }

    public function erase()
    {
        global $cameralife;

        $cameralife->database->Delete('albums', 'id=' . $this->record['id']);
        $cameralife->database->Delete('logs', "record_type='album' AND record_id=" . $this->record['id']);
    }
}
