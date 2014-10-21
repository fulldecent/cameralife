<?php
namespace CameraLife;

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
    public function __construct($path = '/')
    {
        $this->path = '/' . trim($path, '/');
    }

    public function getPrevious()
    {
        global $cameralife;
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
        $conditions[0] = "(path = :1)";
        $binds[1] = $this->path;
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
        $lpath = rtrim($this->path, '/');
        $conditions[0] = "(path LIKE :1)";
        $binds[1] = $lpath . '/%';
        if (!$this->showPrivatePhotos) {
            $conditions[] = 'status = 0';
        }
        $query = $cameralife->database->Select(
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
        //TODO: should not use global CAMERALIFE!
        global $cameralife;

        $conditions = array();
        $binds = array();
        $conditions[0] = "(path = :1)";
        $binds[1] = $this->path;
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
        $conditions[0] = "(path LIKE :1 AND path NOT LIKE :2)";
        $binds[1] = rtrim($this->path, '/') . '/_%';
        $binds[2] = rtrim($this->path, '/') . '/_%/%';
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
        global $cameralife;
        switch ($this->sort) {
            case 'newest':
                $sort = 'created desc';
                break;
            case 'oldest':
                $sort = 'created';
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
        $conditions[0] = "(path LIKE :1)";
        $binds[1] = rtrim($this->path, '/') . '/_%';
        if (!$this->showPrivatePhotos) {
            $conditions[] = 'status = 0';
        }
        $query = $cameralife->database->Select(
            'photos',
            'DISTINCT path, MAX(mtime) as date',
            implode(' AND ', $conditions),
            'GROUP BY path ORDER BY ' . $sort . ' ' . 'LIMIT ' . $this->offset . ',' . $this->pageSize,
            null,
            $binds
        );
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
        //TODO: should not use global CAMERALIFE!
        global $cameralife;
        $retval = array();
        $fileStorePhotosUnmatched = $cameralife->fileStore->listFiles('photo'); // path->basename format
        if (!count($fileStorePhotosUnmatched)) {
            $cameralife->error('No files were found in file store');
        }
        foreach ($fileStorePhotosUnmatched as $unmatchedFilePath => $unmatchedFileBase) {
            //            $unmatchedFilePath = utf8_decode($unmatchedFilePath);
            $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', $unmatchedFilePath));
            //            $normalizedFileStorePaths[utf8_encode($unmatchedFilePath)] = $normalized;
            $normalizedFileStorePaths[$unmatchedFilePath] = $normalized;
        }
        $result = $cameralife->database->Select('photos', 'id,filename,path,fsize', 'status!=9', 'ORDER BY path,filename');

        // Verify each photo in the DB
        while ($dbPhoto = $result->fetchAssoc()) {
            $dbFilename = $dbPhoto['filename'];
            $dbFilePath = rtrim('/' . ltrim($dbPhoto['path'], '/'), '/') . '/' . $dbFilename;
            // DB photo is on disk where expected
            if (isset($fileStorePhotosUnmatched[$dbFilePath])) {
                # Bonus code, if this is local, we can do more verification
                if ($cameralife->getPref('fileStore') == 'local' && $dbPhoto['fsize']) {
                    $dbFileStorePath = $cameralife->fileStore->photoDir . $dbFilePath;
                    if ($dbPhoto['fsize'] != filesize($dbFileStorePath)) {
                        $retval[$photopath] = 'modified';
                    }
                }
                unset ($fileStorePhotosUnmatched[$dbFilePath]);
                continue;
            }

            // Look for a photo in the same place, but with the filename capitalization changed
            $normalizedDBFilePath = strtolower(preg_replace('/[^a-z0-9]/i', '', $dbFilePath));
            $candidates = array_keys($normalizedFileStorePaths, $normalizedDBFilePath);
            if (count($candidates) == 1) {
                //                $candidates[0] = utf8_decode($candidates[0]);
                $retval[$dbFilePath] = array('moved', $candidates[0]);
                unset ($fileStorePhotosUnmatched[$candidates[0]]);
                continue;
            }
            $retval[$dbFilePath] = 'deleted';
        }

        /**
         * $fileStorePhotosUnmatched now contains a list of existing paths that are not in the database
         * Maximum effort will be made to match these to other missing files
         */

        foreach ($fileStorePhotosUnmatched as $newFilePath => $newFileBase) {
            //            $newFilePath = utf8_decode($newFilePath);
            if (preg_match("/^picasa.ini|digikam3.db$/i", $newFileBase)) {
                $retval[$newFileBase] = 'ignored';
                continue;
            }
            if (!preg_match("/.jpg$|.jpeg$|.png$|.gif$/i", $newFileBase)) {
                $retval[$newFileBase] = 'ignored';
                continue;
            }

            $condition = "filename LIKE :fn";
            $binds['fn'] = $newFileBase;
            if ($cameralife->getPref('fileStore') == 'local') {
                $fileStorePath = $cameralife->fileStore->photoDir . $newFilePath;
                $condition .= ' AND fsize=' . filesize($fileStorePath);
            }
            $result = $cameralife->database->Select('photos', 'id, filename, path', $condition, null, null, $binds);

            // Is anything in the fileStore similar?
            if ($dbPhoto = $result->fetchAssoc()) {
                $dbFilePath = rtrim('/' . ltrim($dbPhoto['path'], '/'), '/') . '/' . $dbPhoto['filename'];
            
                if (isset($retval[$dbFilePath]) && $retval[$dbFilePath] != 'deleted') {
                    $retval[$dbFilePath] = array('went one of two places', $newFilePath, $retval[$dbFilePath]);
                    continue;
                } else {
                    $retval[$dbFilePath] = array('moved', $newFilePath);
                    continue;
                }
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
        global $cameralife;
        $retval = array();

        foreach (Folder::findChangesOnDisk() as $filePath => $change) {
            if ($change == 'new') {
                $retval[] = "Added $filePath\n";
                $photoObj = Photo::getPhotoWithFilePath($filePath);
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
                $filename = basename($change[1]);
                //                $filename = end(explode('/', $change[1]));
                $path = '/' . trim(substr($change[1], 0, -strlen($filename)), '/');
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
        global $cameralife;
        $files = $cameralife->fileStore->ListFiles('photo', $this->path, false);
        if (!is_array($files)) {
            return false;
        }

        $fsphotos = $fsdirs = array();
        foreach ($files as $file) {
            if (preg_match("/.jpg$|.jpeg$|.png$|.gif$/i", $file)) {
                $fsphotos[] = $file;
            } else {
                $fsdirs[] = $file;
            }
        }

        $selection = "filename";
        $condition = "path = '" . addslashes($this->path) . "'";
        $result = $cameralife->database->Select('photos', $selection, $condition);
        while ($row = $result->fetchAssoc()) {
            $key = array_search($row['filename'], $fsphotos);
            if ($key === false) {
                return false;
            } else {
                unset ($fsphotos[$key]);
            }
        }

        $selection = "DISTINCT SUBSTRING_INDEX(SUBSTR(path," . (strlen($this->path) + 1) . "),'/',1) AS basename";
        $condition = "path LIKE '" . addslashes($this->path) . "%/' AND status=0";
        $result = $cameralife->database->Select('photos', $selection, $condition);
        while ($row = $result->fetchAssoc()) {
            $key = array_search($row['basename'], $fsdirs);
            if ($key === false) {
                return false;
            } else {
                unset ($fsdirs[$key]);
            }
        }

        return (count($fsphotos) + count($fsdirs) == 0);
    }

    /**
     * getOpenGraph function.
     *
     * @access public
     * @return string[]
     */
    public function getOpenGraph()
    {
        global $cameralife;
        $retval = array();
        $retval['og:title'] = basename($this->path);
        if ($this->path == '/') {
            $retval['og:title'] = '(All photos)';
        }
        $retval['og:type'] = 'website';
        //TODO see https://stackoverflow.com/questions/22571355/the-correct-way-to-encode-url-path-parts
        $retval['og:url'] = $cameralife->baseURL . '/folders' . str_replace(" ", "%20", $this->path);
        if ($cameralife->getPref('rewrite') == 'no') {
            $retval['og:url'] = $cameralife->baseURL . '/folder.php&#63;path=' . str_replace(" ", "%20", $this->path);
        }
        $retval['og:image'] = $cameralife->iconURL('folder');
        $retval['og:image:type'] = 'image/png';
        //$retval['og:image:width'] =
        //$retval['og:image:height'] =
        return $retval;
    }
}
