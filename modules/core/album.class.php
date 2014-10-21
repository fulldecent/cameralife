<?php
namespace CameraLife;

/**
 * Model class for albums
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */
class Album extends Search
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
        //TODO: should not use global CAMERALIFE!    
        global $cameralife;

        $result = $cameralife->database->Select('albums', '*', "id=$id");
        $this->record = $result->fetchAssoc();
        if (!$this->record) {
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
        //TODO: should not use global CAMERALIFE!    
        global $cameralife;

        switch ($this->sort) {
        case 'newest':
            $sort = 'value desc, id desc';
            break;
        case 'oldest':
            $sort = 'value, id';
            break;
        case 'az':
            $sort = 'description';
            break;
        case 'za':
            $sort = 'description desc';
            break;
        case 'popular':
            $sort = 'hits desc';
            break;
        case 'unpopular':
            $sort = 'hits';
            break;
        case 'rand':
            $sort = 'rand()';
            break;
        default:
            $sort = 'id desc';
        }

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
        $query = $cameralife->database->Select(
            'photos',
            'id',
            implode(' AND ', $conditions),
            'ORDER BY ' . $sort . ' ' . 'LIMIT ' . $this->offset . ',' . $this->pageSize,
            'LEFT JOIN exif ON photos.id=exif.photoid and exif.tag="Date taken"',
            $binds
        );
        $photos = array();
        while ($row = $query->fetchAssoc()) {
            $photos[] = new Photo($row['id']);
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
        //TODO: should not use global CAMERALIFE!    
        global $cameralife;

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

        return $cameralife->database->SelectOne(
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
        global $cameralife;

        $receipt = null;
        if ($key != 'hits') {
            $receipt = AuditTrail::log('album', $this->record['id'], $key, $this->record[$key], $value);
        }
        $this->record[$key] = $value;
        $cameralife->database->Update('albums', array($key => $value), 'id=' . $this->record['id']);

        return $receipt;
    }

    public function get($key)
    {
        return $this->record[$key];
    }

    public function getPoster()
    {
        if (Photo::photoExists($this->record['poster_id'])) {
            return new Photo($this->record['poster_id']);
        } else {
            $photos = $this->getPhotos();

            return $photos[0];
        }

    }

    public function setPoster($poster)
    {
        global $cameralife;

        if (!is_numeric($poster)) {
            $cameralife->error("Failed to set poster for album");
        }

        $cameralife->database->SelectOne('photos', 'COUNT(*)', 'status=1 AND id=' . $_GET['poster_id'])
        or $cameralife->error('The selected poster photo does not exist');

        $this->set('poster_id', $_GET['poster_id']);
    }

    public function getTopic()
    {
        return new Topic($this->record['topic']);
    }

    public function erase()
    {
        global $cameralife;

        $cameralife->database->Delete('albums', 'id=' . $this->record['id']);
        $cameralife->database->Delete('logs', "record_type='album' AND record_id=" . $this->record['id']);
    }

    public function getOpenGraph()
    {
        global $cameralife;
        $retval = array();
        $retval['og:title'] = $this->record['name'];
        $retval['og:type'] = 'website';
        $retval['og:url'] = $cameralife->baseURL . '/albums/' . $this->record['id'];
        if ($cameralife->getPref('rewrite') == 'no') {
            $retval['og:url'] = $cameralife->baseURL . '/album.php?id=' . $this->record['id'];
        }
        $photo = $this->getPoster();
        $retval['og:image'] = $photo->getMediaURL('thumbnail');
        $retval['og:image:type'] = 'image/jpeg';
        //$retval['og:image:width'] =
        //$retval['og:image:height'] =
        return $retval;
    }
}
