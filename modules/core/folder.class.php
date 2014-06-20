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
     * @param string $path (default: '/')
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
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $query['start'] = $this->myStart - $this->myLimitCount;
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
            $photos[] = new Photo($row['id']);
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
     * @access private
     */
    private function array_isearch($str, $array)
    {
        foreach ($array as $k => $v) {
            if (strcasecmp($str, $v) == 0) {
                return $k;
            }
        }

        return false;
    }

    /**
     * Updates the DB to match actual contents of photo bucket from fileStore.
     * Returns an array of errors or warning.
     * Tries very hard to avoid creating a new record and deleting an old if in fact the
     * photo was simply moved.
     */
    public static function update()
    {
        global $cameralife;

        $retval = array();
        $filesInStoreNotYetMatchedToDB = $cameralife->fileStore->ListFiles('photo');
        if (!count($filesInStoreNotYetMatchedToDB)) {
            return array('Nothing was found in the fileStore.');
        }
        $result = $cameralife->database->Select('photos', 'id,filename,path,fsize', '', 'ORDER BY path,filename');

        // Verify each photo in the DB
        while ($photo = $result->fetchAssoc()) {
            //TODO FIX DATABASE TO MAKE photos.path like '/a/dir' or '/'
            $filename = $photo['filename'];
            $photopath = trim($photo['path'], '/') . '/' . $filename;
            $photopath = rtrim('/' . ltrim($photo['path'], '/'), '/') . '/' . $filename;

            // Found in correct location
            if (isset($filesInStoreNotYetMatchedToDB[$photopath])) {
                # Bonus code, if this is local, we can do more verification
                if ($cameralife->getPref('fileStore') == 'local' && $photo['fsize']) {
                    $photofile = $cameralife->fileStore->photoDir . "/$photopath";
                    $actualsize = filesize($photofile);
                    // Found, but changed
                    if ($actualsize != $photo['fsize']) {
                        $retval[] = "$photopath was changed, flushing cache";
                        $photoObj = new Photo($photo['id']);
                        $photoObj->revert();
                        $photoObj->loadImage(true); // TRUE == onlyWantEXIF
                        $photoObj->revert(); // saves $photo->record
                        $photoObj->destroy();
                    }
                }
                unset ($filesInStoreNotYetMatchedToDB[$photopath]);
                continue;
            }

            // Look for a photo in the same place, but with the filename capitalization changed
            if (isset($filesInStoreNotYetMatchedToDB[strtolower($photopath)])) {
                unset ($filesInStoreNotYetMatchedToDB[strtolower($photopath)]);
                continue;
            }

            if (isset($filesInStoreNotYetMatchedToDB[strtoupper($photopath)])) {
                unset ($filesInStoreNotYetMatchedToDB[strtoupper($photopath)]);
                continue;
            }

            // Photo not found anywhere
            $retval[] = "$photopath was deleted from filesystem";
            $photoObj = new Photo($photo['id']);
            $photoObj->erase();
        }

        /**
         * $filesInStoreNotYetMatchedToDB now contains a list of existing files that are not in the database
         * Maximum effort will be made to not add these new files to the DB
         */

        foreach ($filesInStoreNotYetMatchedToDB as $newFile => $newbase) {
            if (preg_match("/^picasa.ini|digikam3.db$/i", $newbase)) {
                continue;
            }
            if (!preg_match("/.jpg$|.jpeg$|.png$|.gif$/i", $newbase)) {
                $retval[] = "Skipped $newFile because it is not a JPEG or PNG file";
                continue;
            }

            $newpath = dirname($newFile);
            $condition = "filename LIKE :fn";
            $binds['fn'] = $newbase;
            $result = $cameralife->database->Select('photos', 'id, filename, path', $condition, null, null, $binds);

            // Is anything in the fileStore too similar (given available information) to let this photo in?
            if ($photo = $result->fetchAssoc()) {
                // With the case-insensitive LIKE above, this will handle files renamed only by case
                if (strcasecmp($photo['path'] . $photo['filename'], $newFile) == 0) {
                    $retval[] = $photo['path'] . $photo['filename'] . ' was renamed to ' . $newFile;
                    $cameralife->database->Update('photos', array('filename' => $newbase), 'id=' . $photo['id']);
                    continue;
                }
                $photoFullpath = rtrim('/' . ltrim($photo['path'], '/'), '/') . '/' . $photo['filename'];

                # Bonus code
                $same = false;
                if ($cameralife->getPref('fileStore') == 'local') {
                    $a = file_get_contents($cameralife->fileStore->photoDir . $photoFullpath);
                    $b = file_get_contents($cameralife->fileStore->photoDir . $newFile);
                    if ($a == $b) {
                        $same = true;
                    }
                }

                if ($same) {
                    $error = 'Two photos in your file store are identical, please delete one: ';
                } else {
                    $error = 'Two photos in your file store are too similar, please delete one: ';
                }
                $error .= "$photoFullpath is in the system, $newFile is not";
                $retval[] = $error;
                continue;
            }

            $retval[] = "Added $newFile\n";

            $photoObj = new Photo(array('filename' => $newbase, 'path' => $newpath));
            $photoObj->destroy();
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
