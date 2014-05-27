<?php
namespace CameraLife;

/**
 * Folder class.
 * Access folders on the file system as objects
 *
 * @author    William Entriken <cameralife@phor.net>
 * @access    public
 * @copyright Copyright (c) 2001-2014 William Entriken
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
     * This function should be constructed with either of the parameters Photo or Path.
     * Use 'sync' to compare and verify Folder and disk content
     *
     * <b>Optional </b> When is the latest photo in this folder from, unixtime
     */

    //TODO REMOVE THE SYNC AND DATE PARAM
    public function __construct($path = '/', $sync = false, $date = null)
    {
        parent::__construct();

        $this->path = $path;
        if (!strlen($path)) {
            $this->path = '/';
        }
        $this->date = $date;

        if ($sync && !$this->fsck()) {
            Folder::update();
        }

        //todo use bind here, add a bind parameter to Search
        @$this->mySearchPhotoCondition = "path=:path1";
        $this->mySearchAlbumCondition = "FALSE";
        @$this->mySearchFolderCondition = "path LIKE :path1 AND path NOT LIKE :path2";
        $this->myBinds['path1'] = $this->path;
        $this->myBinds['path2'] = '/%'.$this->path.'/%/';
        if ($this->path == '/') {
            @$this->mySearchFolderCondition = "path LIKE '/%' AND path NOT LIKE '/%/%'";
        }
    }

    public function getPrevious()
    {
        global $cameralife;
        if ($this->myStart > 0) {
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

        return null;
    }

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
     * @param int $count number of Folders to return, or all if 0
     * @return array of Folders
     */
    public function getDescendants($count = 0)
    {
        global $cameralife;
        switch ($this->mySort) {
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

        $result = array();
        $selection = 'DISTINCT path';
        $condition = "status=0 AND path LIKE '" . $this->path . "_%'"; //TODO THIS IS ACTUALLY WRONG
        $extra = "ORDER BY $sort LIMIT $count";
        $family = $cameralife->database->Select('photos', $selection, $condition, $extra);
        while ($youngin = $family->fetchAssoc()) {
            $result[] = new Folder($youngin['path'], false);
        }

        return $result;
    }

    //TODO: DEPRECATED
    public function getChildren()
    {
        return $this->getFolders();
    }

    public function path()
    {
        return $this->path;
    }

    public function basename()
    {
        return basename($this->path);
    }

    public function dirname()
    {
        return dirname($this->path);
    }

    /**
     * @access private
     */
    public function array_isearch($str, $array)
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
