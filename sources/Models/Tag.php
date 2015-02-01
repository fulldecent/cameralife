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
///TODO: throw exception?
            header("HTTP/1.0 404 Not Found");
            $cameralife->error("Album #" . ($original + 0) . " not found.");
        }
        parent::__construct($this->record['term']);
    }

    /**
     * Returns photos per QUERY, privacy, and paging restrictions
     *
     * @access public
     * @return Photo[]
     */
    public function getPhotos()
    {
        $sort = $this->photoSortSqlForOption($this->sort);
        $conditions = array();
        $binds = array();
        $i = 0;
        foreach (preg_split('/\s+/', $this->query) as $queryPart) {
            $conditions[$i] = "(description LIKE :$i OR keywords LIKE :$i)";
            $binds[$i] = '%' . $queryPart . '%';
            $i++;
        }
        if (!$this->showPrivatePhotos) {
            $conditions[] = 'status = 0';
        }
        $query = Database::select(
            'photos',
            'id',
            implode(' AND ', $conditions),
            'ORDER BY ' . $sort . ' ' . 'LIMIT ' . $this->offset . ',' . $this->pageSize,
            'LEFT JOIN exif ON photos.id=exif.photoid and exif.tag="Date taken"',
            $binds
        );
        $photos = array();
        while ($row = $query->fetchAssoc()) {
            $photos[] = Photo::getPhotoWithID($row['id']);
        }

        return $photos;
    }

    /**
     * Counts photos per QUERY, and privacy restrictions
     *
     * @access public
     * @return int
     */
    public function getPhotoCount()
    {
        $conditions = array();
        $binds = array();
        $i = 0;
        foreach (preg_split('/\s+/', $this->query) as $queryPart) {
            $conditions[$i] = "(description LIKE :$i OR keywords LIKE :$i)";
            $binds[$i] = '%' . $queryPart . '%';
            $i++;
        }
        if (!$this->showPrivatePhotos) {
            $conditions[] = 'status = 0';
        }

        return Database::selectOne(
            'photos',
            'COUNT(*)',
            implode(' AND ', $conditions),
            null,
            null,
            $binds
        );
    }

    public function set($key, $value)
    {
        $receipt = null;
        if ($key != 'hits') {
            $receipt = AuditTrail::createAuditTrailForChange('album', $this->record['id'], $key, $this->record[$key], $value);
        }
        $this->record[$key] = $value;
        Database::update('albums', array($key => $value), 'id=' . $this->record['id']);

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

        $cameralife->database->SelectOne('photos', 'COUNT(*)', 'status=1 AND id=' . $_GET['poster_id'])
        or $cameralife->error('The selected poster photo does not exist');

        $this->set('poster_id', $_GET['poster_id']);
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
