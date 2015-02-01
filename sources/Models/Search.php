<?php
namespace CameraLife\Models;

/**
 * Returns photos, tags, and folders as restricted by QUERY and paging options
 * @author William Entriken <cameralife@phor.net>
 * @access public
 * @copyright 2001-2014 William Entriken
 */
class Search extends IndexedModel
{
    /**
     * A search term by which to restrict results
     *
     * (default value: '')
     *
     * @var    string
     * @access protected
     */
    public $query = '';

    /**
     * The order to return results by, must be an option from `Search::sortOptions()`
     *
     * (default value: 'newest')
     *
     * @var    string
     * @access public
     */
    public $sort = 'newest';

    /**
     * Whether we should show photos that are NOT status=0
     *
     * (default value: false)
     *
     * @var    bool
     * @access public
     */
    public $showPrivatePhotos = false;

    /**
     * The index of results to show (zero-based)
     *
     * (default value: 0)
     *
     * @var    int
     * @access protected
     */
    protected $offset = 0;

    /**
     * The maximum number of results to return
     *
     * (default value: 20)
     *
     * @var    int
     * @access protected
     */
    protected $pageSize = 20;

    /**
     * __construct function.
     *
     * @access public
     * @param  mixed $query
     * @return void
     */
    function __construct($query = '')
    {
//TODO this can actually just run from the parent class
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

    protected function photoSortSqlForOption($option)
    {
        if ($option == 'newest') {
            return 'value desc, photos.id desc';
        } else if ($option == 'oldest') {
            return 'value, photos.id';
        } else if ($option == 'az') {
            return 'description';
        } else if ($option == 'za') {
            return 'description desc';
        } else if ($option == 'popular') {
            return 'hits desc';
        } else if ($option == 'unpopular') {
            return 'hits, photos.id';
        } else if ($option == 'rand') {
            return 'rand()';
        }
        return 'photos.id desc';
    }

    protected function tagSortSqlForOption($option)
    {
        if ($option == 'newest') {
            return 'albums.id desc';
        } else if ($option == 'oldest') {
            return 'albums.id';
        } else if ($option == 'az') {
            return 'description';
        } else if ($option == 'za') {
            return 'description desc';
        } else if ($option == 'popular') {
            return 'albums.hits desc';
        } else if ($option == 'unpopular') {
            return 'albums.hits';
        } else if ($option == 'rand') {
            return 'rand()';
        }
        return 'albums.id desc';
    }
    
    protected function folderSortSqlForOption($option)
    {
        if ($option == 'newest') {
            return 'id desc';
        } else if ($option == 'oldest') {
            return 'id';
        } else if ($option == 'az') {
            return 'path';
        } else if ($option == 'za') {
            return 'path desc';
        } else if ($option == 'popular') {
            return 'hits desc';
        } else if ($option == 'unpopular') {
            return 'hits';
        } else if ($option == 'rand') {
            return 'rand()';
        }
        return 'id desc';
    }    

    /**
     * Sets the offset and number of results to return
     *
     * @access public
     * @param  mixed $start
     * @param  int   $pagesize (default: 20)
     * @return void
     */
    public function setPage($start, $pageSize = 20)
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
        $sort = $this->photoSortSqlForOption($this->sort);
        $conditions = array();
        $binds = array();
        $i = 0;
        foreach (preg_split('/\s+/', $this->query) as $queryPart) {
            $conditions[$i] = "(description LIKE :$i OR keywords LIKE :$i)";
            $binds[$i++] = '%' . $queryPart . '%';
        }
        if (!$this->showPrivatePhotos) {
            $conditions[] = 'status = 0';
        }
        $query = Database::Select(
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
     * Returns tags per QUERY, and paging restrictions
     *
     * @access public
     * @return Photo[]
     */
    public function getTags()
    {
        $sort = $this->tagSortSqlForOption($this->sort);
        $conditions = array();
        $binds = array();
        $i = 0;
        foreach (preg_split('/\s+/', $this->query) as $queryPart) {
            $conditions[$i] = "(name LIKE :$i)";
            $binds[$i++] = '%' . $queryPart . '%';
        }
        $query = Database::select(
            'albums',
            'id',
            implode(' AND ', $conditions),
            'ORDER BY ' . $sort . ' ' . 'LIMIT ' . $this->offset . ',' . $this->pageSize,
            null,
            $binds
        );

        $tags = array();
        while ($row = $query->fetchAssoc()) {
            $tags[] = new Tag($row['id']);
        }

        return $tags;
    }

    /**
     * Returns folders per QUERY, privacy, and paging restrictions
     *
     * @access public
     * @return Photo[]
     */
    public function getFolders()
    {
        $sort = $this->folderSortSqlForOption($this->sort);
        $conditions = array();
        $binds = array();
        $i = 0;
        foreach (preg_split('/\s+/', $this->query) as $queryPart) {
            $conditions[$i] = "(path LIKE :$i)";
            $binds[$i++] = '%' . $queryPart . '%';
        }
        if (!$this->showPrivatePhotos) {
            $conditions[] = 'status = 0';
        }
        $query = Database::select(
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

    /**
     * Counts tags per QUERY, restrictions
     *
     * @access public
     * @return int
     */
    public function getTagCount()
    {
        $conditions = array();
        $binds = array();
        $i = 0;
        foreach (preg_split('/\s+/', $this->query) as $queryPart) {
            $conditions[$i] = "(name LIKE :$i)";
            $binds[$i] = '%' . $queryPart . '%';
            $i++;
        }

        return Database::selectOne(
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

        return Database::selectOne(
            'photos',
            'COUNT(DISTINCT path)',
            implode(' AND ', $conditions),
            null,
            null,
            $binds
        );
    }
}
