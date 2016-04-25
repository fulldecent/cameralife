<?php
namespace CameraLife\Models;

/**
 * Folder class.
 * Access folders on the file system as objects
 *
 * @author    William Entriken <cameralife@phor.net>
 * @access    public
 * @copyright 2001-2014 William Entriken
 * @extends   Search
 */
class Folder extends Search
{
    /**
     * path
     * Like: '/' or '/afolder' or '/parent/child'
     *
     * @var    string
     * @access public
     */
    public $path;

    /**
     * __construct function.
     *
     * @access public
     * @param  string $path (default: '/')
     * @return void
     */
    public function __construct($path)
    {
        parent::__construct();
        $this->path = '/' . trim($path, '/');
        $this->id = $path;
    }

    public static function getRootFolder()
    {
        return new self('/');
    }

    public function getPrevious()
    {
        if (!$this->offset) {
            return null;
        }
        if ($cameralife->getPref('rewrite') == 'yes') {
            $href = $cameralife->baseURL . '/folders' . str_replace(" ", "%20", $this->path);
        } else {
            $href = $cameralife->baseURL . '/folder.php&#63;path=' . str_replace(" ", "%20", $this->path);
        }
        parse_str(parse_url($href, PHP_URL_QUERY), $query);
        $query['start'] = $this->offset - $this->pageSize;
        $href = preg_replace('/\?.*/', '', $href) . '?' . http_build_query($query);
        return $href;
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
        $conditions[0] = "(path = :1)";
        $binds[1] = $this->path;
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
        $lpath = rtrim($this->path, '/');
        $conditions[0] = "(path LIKE :1)";
        $binds[1] = $lpath . '/%';
        if (!$this->showPrivatePhotos) {
            $conditions[] = 'status = 0';
        }
        $query = Database::select(
            'photos',
            'DISTINCT substring_index(substr(path,'.(strlen($lpath)+2)."), '/', 1) as basename",
            implode(' AND ', $conditions),
            'GROUP BY path ORDER BY ' . $sort . ' ' . 'LIMIT ' . $this->offset . ',' . $this->pageSize,
            null,
            $binds
        );

        $folders = array();
        while ($row = $query->fetchAssoc()) {
            $folders[] = new Folder($lpath . '/' . $row['basename']);
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
        $conditions[0] = "(path = :1)";
        $binds[1] = $this->path;
        if (!$this->showPrivatePhotos) {
            $conditions[] = 'status = 0';
        }
        return Database::selectOne(
            'photos',
            'COUNT(id )',
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
        $conditions[0] = "(path LIKE :1 AND path NOT LIKE :2)";
        $binds[1] = rtrim($this->path, '/') . '/_%';
        $binds[2] = rtrim($this->path, '/') . '/_%/%';
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

    protected function folderSortSqlForOption($option)
    {
        if ($option == 'newest') {
            return 'date desc';
        } elseif ($option == 'oldest') {
            return 'date';
        } elseif ($option == 'az') {
            return 'path';
        } elseif ($option == 'za') {
            return 'path desc';
        } elseif ($option == 'popular') {
            return 'hits desc';
        } elseif ($option == 'unpopular') {
            return 'hits';
        } elseif ($option == 'rand') {
            return 'rand()';
        }
        return 'id desc';
    }

    /**
     * An array of parent, grandparent... top level folder
     *
     * @access public
     * @return void
     */
    public function getAncestors()
    {
        $retval = array();
        $path = $this->path;
        while ($path != '/') {
            $path = dirname($path);
            $retval[] = new Folder($path);
        }
        return array_reverse($retval);
    }

    /**
     * Returns descendant folders per QUERY, privacy, and paging restrictions
     *
     * @access public
     * @return Photo[]
     */
    public function getDescendants()
    {
        $sort = $this->folderSortSqlForOption($this->sort);
        $conditions = array();
        $binds = array();
        $conditions[0] = "(path LIKE :1)";
        $binds[1] = rtrim($this->path, '/') . '/_%';
        if (!$this->showPrivatePhotos) {
            $conditions[] = 'status = 0';
        }
        $query = Database::select(
            'photos',
            'DISTINCT path, MAX(mtime) as date',
            implode(' AND ', $conditions),
            'GROUP BY path ORDER BY ' . $sort . ' ' . 'LIMIT ' . $this->offset . ',' . $this->pageSize,
            null,
            $binds
        );
        $result = array();
        while ($youngin = $query->fetchAssoc()) {
            $result[] = new Folder($youngin['path']);
        }
        return $result;
    }

    /**
     * Produces a list of changes from the database representation to the disk
     *
     * @access public
     * @static
     * @return array with PATHs as index and value one of:
     *   'new'
     *   'modified'
     *   'deleted'
     *   'ignored'
     *   array: ['moved', NEWPATH]
     *   array: ['went one of two places', ???]
     */
    public static function findChangesOnDisk()
    {
        $retval = array();
                
        $fileStore = FileStore::fileStoreWithName('photo');
        $fileStoreNewPhotos = $fileStore->listFiles(); // path->basename format
        if (!count($fileStoreNewPhotos)) {
            throw new \Exception('No files were found in file store');
        }
        foreach ($fileStoreNewPhotos as $unmatchedFilePath => $unmatchedFileBase) {
            $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', $unmatchedFilePath));
            $normFileStorePaths[$unmatchedFilePath] = $normalized;
        }
        $result = Database::select('photos', 'id,filename,path,fsize,status', '', 'ORDER BY path,filename');

        // Verify each photo in the DB
        while ($dbPhoto = $result->fetchAssoc()) {
            $dbFilePath = rtrim('/' . ltrim($dbPhoto['path'], '/'), '/') . '/' . $dbPhoto['filename'];
            // DB photo is on disk where expected
            if (isset($fileStoreNewPhotos[$dbFilePath])) {
                unset ($fileStoreNewPhotos[$dbFilePath]);
                unset ($normFileStorePaths[$dbFilePath]);
                continue;
            }

            // Look for a photo in the same place, but with the filename capitalization changed
            $normalizedDBFilePath = strtolower(preg_replace('/[^a-z0-9]/i', '', $dbFilePath));
            $candidates = array_keys($normFileStorePaths, $normalizedDBFilePath);
            if (count($candidates) == 1) {
                $retval[$dbFilePath] = array('moved', $candidates[0]);
                unset ($fileStoreNewPhotos[$candidates[0]]);
                unset ($normFileStorePaths[$candidates[0]]);
                continue;
            }
            if ($dbPhoto['status'] == 9) {
                continue;
            } else {
                $retval[$dbFilePath] = 'deleted';
            }
        }

        /**
         * $fileStoreNewPhotos now contains a list of existing paths that are not in the database
         * Maximum effort will be made to match these to other missing files
         */

        foreach ($fileStoreNewPhotos as $newFilePath => $newFileBase) {
            //            $newFilePath = utf8_decode($newFilePath);
            if (preg_match("/^picasa.ini|digikam3.db$/i", $newFileBase)) {
                $retval[$newFileBase] = 'ignored';
                continue;
            }
            if (!preg_match("/.jpg$|.jpeg$|.png$|.gif$/i", $newFileBase)) {
                $retval[$newFileBase] = 'ignored';
                continue;
            }
            $retval[$newFilePath] = 'new';
        }

        return $retval;
    }

    /**
     * Updates the DB to match actual contents of photo bucket from fileStore.
     * Returns an array of errors or warning.
     *
     * @access public
     * @static
     * @return string[]
     */
    public static function update()
    {
        //TODO: NEED TEST CASES FOR THIS AND ACTUAL TESTING!!!!!!!
        // move files / delete files / readd files (undelete) / UTF filenames
        $retval = array();
        foreach (Folder::findChangesOnDisk() as $filePath => $change) {
            if ($change == 'new') {
                $retval[] = "Added $filePath\n";
                $filename = basename($filePath);
                $path = '/' . trim(substr($filePath, 0, -strlen($filename)), '/');
                $photoObj = Photo::createPhotoWithRecord(['filename'=>$filename,'path'=>$path]);
                $photoObj->destroy();
            } elseif ($change == 'modified') {
                $retval[] = "$filePath was changed, flushing cache";
                $photoObj = Photo::getPhotoWithFilePath($filePath);
                $photoObj->revert();
            } elseif ($change == 'deleted') {
                $retval[] = "$filePath was deleted from filesystem";
                $photoObj = Photo::getPhotoWithFilePath($filePath);
                $photoObj->erase();
            } elseif ($change == 'ignored') {
                $retval[] = "$filePath was skipped as it is not jpg/png";
            } elseif (is_array($change) && $change[0] == 'moved') {
                $retval[] = "$filePath was moved to {$change[1]}";
                // mb_basename = end(explode('/',$file)) // http://php.net/manual/en/function.basename.php
                // pathinfo is better than basename with unicode utf8
                $photoObj = Photo::getPhotoWithFilePath($filePath);
                // $filename = basename($change[1]); DOES NOT WORK WITH CJK AND UTF-8
                $filename = end(explode('/', $change[1]));
                $path = '/' . trim(mb_substr($change[1], 0, -mb_strlen($filename)), '/');
                $photoObj->set('path', $path);
                $photoObj->set('filename', $filename);
            } else {
                $retval[] = "Something happened with $filePath / " . print_r($change, true);
            }
        }
        return $retval;
    }

    /**
     * Does a quick compare of Database and FileStore and checks if they are same
     *
     * @return true or false
     */
    public function fsck()
    {
        $fileStore = FileStore::fileStoreWithName('photo');
        $fileStoreNewPhotos = $fileStore->listFiles(); // path->basename format
        if (!count($fileStoreNewPhotos)) {
            throw new \Exception('No files were found in file store');
        }
        $result = Database::select('photos', 'id,filename,path,fsize', 'status!=9', 'ORDER BY path,filename');

        // Verify each photo in the DB
        while ($dbPhoto = $result->fetchAssoc()) {
            $dbFilePath = rtrim('/' . ltrim($dbPhoto['path'], '/'), '/') . '/' . $dbPhoto['filename'];
            if (isset($fileStoreNewPhotos[$dbFilePath])) {
                unset ($fileStoreNewPhotos[$dbFilePath]);
                continue;
            }
            return false;
        }
        return count($fileStoreNewPhotos) == 0;
    }
}
