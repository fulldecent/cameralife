<?php
namespace CameraLife;

/**
 * Returns photos, albums, and folders as restricted by QUERY and paging options
 * @author William Entriken <cameralife@phor.net>
 * @access public
 * @copyright 2001-2014 William Entriken
 */
class Search extends View
{
    /**
     * A search term by which to restrict results
     *
     * (default value: '')
     *
     * @var string
     * @access protected
     */
    public $query = '';

    /**
     * The order to return results by, must be an option from `Search::sortOptions()`
     *
     * (default value: 'newest')
     *
     * @var string
     * @access public
     */
    public $sort = 'newest';

    /**
     * Whether we should show photos that are NOT status=0
     *
     * (default value: false)
     *
     * @var bool
     * @access public
     */
    public $showPrivatePhotos = false;
    
    /**
     * The index of results to show (zero-based)
     *
     * (default value: 0)
     *
     * @var int
     * @access protected
     */
    protected $offset = 0;

    /**
     * The maximum number of results to return
     *
     * (default value: 12)
     *
     * @var int
     * @access protected
     */
    protected $pageSize = 12;

    /**
     * __construct function.
     *
     * @access public
     * @param mixed $query
     * @return void
     */
    function __construct($query = '')
    {
        $this->query = $query;
    }

    /**
     * Show available sort options
     *
     * @access public
     * @static
     * @return void
     */
    public static function sortOptions()
    {
        $retval = array();
        $retval[] = array('newest', 'Newest First');
        $retval[] = array('oldest', 'Oldest First');
        $retval[] = array('az', 'Alphabetically (A-Z)');
        $retval[] = array('za', 'Alphabetically (Z-A)');
        $retval[] = array('popular', 'Popular First');
        $retval[] = array('unpopular', 'Unpopular First');
        $retval[] = array('rand', 'Random');
        return $retval;
    }

    /**
     * Sets the offset and number of results to return
     *
     * @access public
     * @param mixed $start
     * @param int $pagesize (default: 12)
     * @return void
     */
    public function setPage($start, $pageSize = 12)
    {
        $this->offset = $start;
        $this->pageSize = $pageSize;
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
     * Returns albums per QUERY, and paging restrictions
     *
     * @access public
     * @return Photo[]
     */
    public function getAlbums()
    {
//TODO: should not use global CAMERALIFE!    
        global $cameralife;

        switch ($this->sort) {
            case 'newest':
                $sort = 'albums.id desc';
                break;
            case 'oldest':
                $sort = 'albums.id';
                break;
            case 'az':
                $sort = 'description';
                break;
            case 'za':
                $sort = 'description desc';
                break;
            case 'popular':
                $sort = 'albums.hits desc';
                break;
            case 'unpopular':
                $sort = 'albums.hits';
                break;
            case 'rand':
                $sort = 'rand()';
                break;
            default:
                $sort = 'albums.id desc';
        }

        $conditions = array();
        $binds = array();
        $i = 0;
        foreach (preg_split('/\s+/', $this->query) as $queryPart) {
            $conditions[$i] = "(name LIKE :$i)";
            $binds[$i] = '%' . $queryPart . '%';
            $i++;
        }
        $query = $cameralife->database->Select(
            'albums',
            'id',
            implode(' AND ', $conditions),
            'ORDER BY ' . $sort . ' ' . 'LIMIT ' . $this->offset . ',' . $this->pageSize,
            null,
            $binds
        );

        $albums = array();
        while ($row = $query->fetchAssoc()) {
            $albums[] = new Album($row['id']);
        }

        return $albums;
    }

    /**
     * Returns folders per QUERY, privacy, and paging restrictions
     *
     * @access public
     * @return Photo[]
     */
    public function getFolders()
    {
//TODO: should not use global CAMERALIFE!    
        global $cameralife;
        switch ($this->sort) {
            case 'newest':
                $sort = 'id desc';
                break;
            case 'oldest':
                $sort = 'id';
                break;
            case 'az':
                $sort = 'path';
                break;
            case 'za':
                $sort = 'path desc';
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
            $conditions[$i] = "(path LIKE :$i)";
            $binds[$i] = '%' . $queryPart . '%';
            $i++;
        }
        if (!$this->showPrivatePhotos) {
            $conditions[] = 'status = 0';
        }
        $query = $cameralife->database->Select(
            'photos',
            'path, MAX(mtime) as date',
            implode(' AND ', $conditions),
            'GROUP BY path ORDER BY ' . $sort . ' ' . 'LIMIT ' . $this->offset . ',' . $this->pageSize,
            null,
            $binds
        );
        
        $folders = array();
        while ($row = $query->fetchAssoc()) {
            $folders[] = new Folder($row['path'], false, $row['date']);
        }

        return $folders;
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
  
    /**
     * Counts albums per QUERY, restrictions
     *
     * @access public
     * @return int
     */
    public function getAlbumCount()
    {
//TODO: should not use global CAMERALIFE!    
        global $cameralife;

        $conditions = array();
        $binds = array();
        $i = 0;
        foreach (preg_split('/\s+/', $this->query) as $queryPart) {
            $conditions[$i] = "(name LIKE :$i)";
            $binds[$i] = '%' . $queryPart . '%';
            $i++;
        }

        return $cameralife->database->SelectOne(
            'albums',
            'COUNT(*)',
            implode(' AND ', $conditions),
            null,
            null,
            $binds
        );
    }
    
    /**
     * Counts folders per QUERY, and privacy restrictions
     *
     * @access public
     * @return int
     */
    public function getFolderCount()
    {
//TODO: should not use global CAMERALIFE!    
        global $cameralife;

        $conditions = array();
        $binds = array();
        $i = 0;
        foreach (preg_split('/\s+/', $this->query) as $queryPart) {
            $conditions[$i] = "(path LIKE :$i)";
            $binds[$i] = '%' . $queryPart . '%';
            $i++;
        }
        if (!$this->showPrivatePhotos) {
            $conditions[] = 'status = 0';
        }

        return $cameralife->database->SelectOne(
            'photos',
            'COUNT(DISTINCT path)',
            implode(' AND ', $conditions),
            null,
            null,
            $binds
        );
    }

    /**
     * Create array of OG data
     *
     * @access public
     * @return string[]
     */
    public function getOpenGraph()
    {
        global $cameralife;
        $retval = array();
        $retval['og:title'] = 'Search for: ' . $this->query;
        $retval['og:type'] = 'website';
        //TODO see https://stackoverflow.com/questions/22571355/the-correct-way-to-encode-url-path-parts
        $retval['og:url'] = $cameralife->baseURL . '/search.php?q=' . str_replace(" ", "%20", $this->query);
        $retval['og:image'] = $cameralife->iconURL('search');
        $retval['og:image:type'] = 'image/png';
        //$retval['og:image:width'] =
        //$retval['og:image:height'] =
        return $retval;
    }
}
