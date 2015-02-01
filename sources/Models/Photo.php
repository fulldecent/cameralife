<?php
namespace CameraLife\Models;

/**
 * Class Photo provides a front end to working with photos
 *
 * @author    William Entriken <cameralife@phor.net>
 * @access    public
 * @version
 * @copyright 2001-2009 William Entriken
 */
class Photo extends IndexedModel
{
    public $record, $image;

    /**
     * The file extension, e.g. png
     *
     * @var    String
     * @access public
     */
    public $extension;

    /**
     * context
     * an Album, Search or Folder of where the user came from to get to this photo
     *
     * @var    mixed
     * @access private
     */
     ///TODO TEMPORARY
    public $context;

    /**
     * contextPhotos
     * An ordered set of photos in the same context as this one
     *
     * @var    mixed
     * @access private
     */
    private $contextPhotos;

    /**
     * contextPrev
     * the previous photo in context
     *
     * @var    mixed
     * @access private
     */
    private $contextPrev;

    /**
     * contextNext
     * the next photo in contex
     *
     * @var    mixed
     * @access private
     */
    private $contextNext;

    //todo I hate this function
    /**
     * Loads a photo with a given FILEPATH
     *
     * @access public
     * @static
     * @param  array photo record from the database
     * @return Photo
     */
    private static function getPhotoWithRecord($record)
    {
        global $cameralife;
        $retval = new Photo();
        $retval->record = $record;
        if (isset($retval->record['filename'])) {
            $pathParts = pathinfo($retval->record['filename']);
        }
        if (isset($pathParts['extension'])) {
            $retval->extension = strtolower($pathParts['extension']);
        }
        $retval->id = $retval->record['id'];
        return $retval;
    }

    /**
     * Creates a photo with given record
     *
     * @access public
     * @static
     * @param  array $record
     * @return Photo
     */
    public static function createPhotoWithRecord($record)
    {
        global $cameralife;
        $defaults['description'] = 'unnamed';
        $defaults['status'] = '0';
        $defaults['created'] = date('Y-m-d');
        $defaults['modified'] = '0';
        $defaults['mtime'] = '0';
        $finalRecord = array_merge($defaults, $record);
        $finalRecord['id'] = Database::insert('photos', $finalRecord);
        $retval = Photo::getPhotoWithRecord($finalRecord);
        return $retval;
    }

    /**
     * Loads a photo with a given FILEPATH
     *
     * @access public
     * @static
     * @param  mixed $filePath string like /folder/photo.jpg
     * @return Photo
     */
    public static function getPhotoWithFilePath($filePath)
    {
        global $cameralife;
        $filename = basename($filePath);
        $path = '/' . trim(substr($filePath, 0, -strlen($filename)), '/');
        $bind = array('f'=>$filename, 'p'=>$path);
        $result = Database::select('photos', '*', "filename=:f AND path=:p", null, null, $bind);
        $record = $result->fetchAssoc();
        if (!$record) {
            throw new \Exception('Photo not found in database with filePath ' . $filePath);
        }
        return Photo::getPhotoWithRecord($record);
    }

    /**
     * Loads a photo with a given id
     *
     * @access public
     * @static
     * @param  integer
     * @return Photo
     */
    public static function getPhotoWithID($id)
    {
        // TODO most calls to this function are better served by getPhotoWithRecord
        global $cameralife;
        $bind = array('i'=>$id);
        $result = Database::select('photos', '*', "id=:i", null, null, $bind);
        $result->id = $id;
        $record = $result->fetchAssoc()
        or $cameralife->error("Photo #$id not found");
        return Photo::getPhotoWithRecord($record);
    }

    /**
     * __construct function.
     * 
     * @access protected
     * @param  mixed $original (default: null)
     * @return void
     */
    protected function __construct($original = null)
    {
        $this->context = false;
        $this->contextPrev = false;
        $this->contextNext = false;
        $this->contextPhotos = array();
        $this->EXIF = array();
    }

    //////////////////////////////////////////////////////////

    public static function photoExists($id)
    {
        $numMatchingPhotos = Database::selectOne('photos', 'COUNT(*)', 'id=:id', null, null, ['id'=>$id]);
        return $numMatchingPhotos > 0;
    }

    public function set($key, $value, User $user = null)
    {
        $receipt = null;
        $this->record[$key] = $value;
        Database::update('photos', array($key => $value), 'id=' . $this->record['id']);
        if (isset($user)) {
            $receipt = AuditTrail::createAuditTrailForChange($user, 'photo', $this->record['id'], $key, $this->record[$key], $value);
        }
        return $receipt;
    }

    public function get($key)
    {
        if (isset($this->record[$key])) {
            return $this->record[$key];
        } else {
            return null;
        }
    }

    /// Initialize <var>$this->image</var> variable and collect fsize and $this->loadEXIF if possible
    /// Loads the original image, not the modified
    public function loadImage()
    {
        global $cameralife;
        if (isset($this->image)) {
            return;
        }
        $fullpath = rtrim('/' . ltrim($this->record['path'], '/'), '/') . '/' . $this->record['filename'];
        $fileStore = FileStore::fileStoreWithName('photo');
        list ($file, $temp, $this->record['mtime']) = $fileStore->getFile($fullpath);
        $this->record['fsize'] = filesize($file);
        $this->record['created'] = date('Y-m-d', $this->record['mtime']);
        $this->loadEXIF($file);

        $this->image = new Image($file)
        or $cameralife->error("Bad photo load: $file");
        if (!$this->image->check()) {
            $cameralife->error("Bad photo processing: $file");
        }

        if ($temp) {
            unlink($file);
        }
    }

    /// Scale image to all needed sizes and save in file store, update image/tn sizes
    /// also update fsize if this is unmodified.
    public function generateThumbnail()
    {
        $this->loadImage(); // sets $this->EXIF and $this-record
        if (Preferences::valueForModuleWithKey('CameraLife', 'autorotate')  == 'yes'
            && (!$this->record['modified'] || $this->record['modified'] == '1')
        ) {
            $this->rotateEXIF();
        }

        $activeImage = $this->image;

        // Apply all modifications
        if ($this->record['modified']) {
            $modArray = json_decode($this->record['modified'], true);
            $rotation = isset($modArray['rotate']) ? $modArray['rotate'] : 0;
            $activeImage->rotate($rotation);
            $tempfile = tempnam(sys_get_temp_dir(), 'cameralife_mod');
            $activeImage->save($tempfile);
            $filename = '/' . $this->record['id'] . '_mod.' . $this->extension;
            $store = FileStore::fileStoreWithName('other');
            $store->putFile($filename, $tempfile, $this->record['status'] != 0);
            //todo warning secure!            
        }

        $imagesize = $activeImage->getSize();
        $this->record['width'] = $imagesize[0];
        $this->record['height'] = $imagesize[1];
        
        $thumbSize = Preferences::valueForModuleWithKey('CameraLife', 'thumbsize');
        $scaledSize = Preferences::valueForModuleWithKey('CameraLife', 'scaledsize');
        $optionSizes = Preferences::valueForModuleWithKey('CameraLife', 'optionsizes');

        $sizes = array($thumbSize, $scaledSize);
        preg_match_all('/[0-9]+/', $optionSizes, $matches);
        $sizes = array_merge($sizes, $matches[0]);
        rsort($sizes);
        $files = array();

        foreach ($sizes as $cursize) {
            $tempfile = tempnam(sys_get_temp_dir(), 'cameralife_' . $cursize);
            $dims = $activeImage->resize($tempfile, $cursize);
            $filename = '/' . $this->record['id'] . '_' . $cursize . '.' . $this->extension;
            $fileStore = FileStore::fileStoreWithName('other');
            $fileStore->putFile($filename, $tempfile, $this->record['status'] != 0);
            unlink($file);
            if ($cursize == $thumbSize) {
                $this->record['tn_width'] = $dims[0];
                $this->record['tn_height'] = $dims[1];
            }
        }
        Database::update('photos', $this->record, 'id=' . $this->record['id']);
    }

    private function deleteThumbnails()
    {
        //todo update        
        $cameralife->fileStore->EraseFile('other', '/' . $this->record['id'] . '_mod.' . $this->extension);
        $cameralife->fileStore->EraseFile(
            'other',
            '/' . $this->record['id'] . '_' . $cameralife->getPref('scaledsize') . '.' . $this->extension
        );
        $cameralife->fileStore->EraseFile(
            'other',
            '/' . $this->record['id'] . '_' . $cameralife->getPref('thumbsize') . '.' . $this->extension
        );
    }

    // Remove all modifications from the photo
    public function revert()
    {
        global $cameralife;
        if (!$this->record['modified']) {
            return;
        }
        $this->record['modified'] = null;
        $cameralife->database->Update('photos', $this->record, 'id=' . $this->record['id']);
        $this->deleteThumbnails();
    }

    public function rotate($angle)
    {
        global $cameralife;
        $modifications = json_decode($this->record['modified'], true);
        if (!is_array($modifications)) {
            $modifications = array();
        }
        $rotation = isset($modifications['rotate']) ? $modifications['rotate'] : 0;
        $rotation = ($rotation + $angle) % 360;
        $modifications['rotate'] = $rotation;
        $this->record['modified'] = json_encode($modifications);
        $cameralife->database->Update('photos', $this->record, 'id=' . $this->record['id']);
        $this->deleteThumbnails();
    }

    public function rotateEXIF()
    {
        global $cameralife;
        $this->loadImage(); // sets $this->EXIF and $this-record
        if (!isset($this->EXIF['Orientation'])) {
            return;
        }
        if ($this->EXIF['Orientation'] == 3) {
            $this->rotate(180);
        } elseif ($this->EXIF['Orientation'] == 6) {
            $this->rotate(90);
        } elseif ($this->EXIF['Orientation'] == 8) {
            $this->rotate(270);
        }
    }

    public function erase()
    {
        //global $cameralife;
        $this->set('status', 9);
        /*
        ///TODO
        $cameralife->database->Delete('photos','id='.$this->record['id']);
        $cameralife->database->Delete('logs',"record_type='photo' AND record_id=".$this->record['id']);
        $cameralife->database->Delete('ratings',"id=".$this->record['id']);
        $cameralife->database->Delete('comments',"photo_id=".$this->record['id']);
        $cameralife->database->Delete('exif',"photoid=".$this->record['id']);
        */
        $this->destroy();
    }

    public function destroy()
    {
        if ($this->image) {
            $this->image->Destroy();
        }
    }

    public function getMediaURL($format = 'thumbnail')
    {
        if ($format == 'photo' || $format == '') {
            if ($this->get('modified')) {
                $path = "/{$this->record['id']}_mod.{$this->extension}";
                $store = 'other';
            } else {
                $path = "/{$this->record['path']}{$this->record['filename']}";
                $store = 'photos';
            }
        } elseif ($format == 'scaled') {
            $thumbSize = Preferences::valueForModuleWithKey('CameraLife', 'scaledsize');
            $path = "/{$this->record['id']}_{$thumbSize}.{$this->extension}";
            $store = 'other';
        } elseif ($format == 'thumbnail') {
            $thumbSize = Preferences::valueForModuleWithKey('CameraLife', 'thumbsize');
            $path = "/{$this->record['id']}_{$thumbSize}.{$this->extension}";
            $store = 'other';
        } elseif (is_numeric($format)) {
            $valid = preg_split('/[, ]+/', Preferences::valueForModuleWithKey('CameraLife', 'optionsizes'));
            if (!in_array($format, $valid)) {
                throw new \Exception('This image size has not been allowed');
            }
            $path = "/{$this->record['id']}_{$format}.{$this->extension}";
            $store = 'other';
        } else {
            throw new \Exception('Bad format parameter');
        }
        $fileStore = FileStore::fileStoreWithName($store);
        $url = $fileStore->getUrl($path);
        if ($url) {
            return $url;
        }
        $url = constant('BASE_URL') . "/media/{$this->record['id']}.{$this->extension}?scale={$format}&ver={$this->record['mtime']}";
        if (Preferences::valueForModuleWithKey('CameraLife', 'rewrite') == 'no') {
            $url = constant('BASE_URL') . "/index.php?page=Media&id={$this->record['id']}&scale={$format}&ver={$this->record['mtime']}";
        }
        return $url;
    }

    /**
     * isCacheMissing function.
     * Return true if thumbnail is missing or if needed _mod is missing
     *
     * @access public
     * @return bool
     */
    public function isCacheMissing()
    {
        if ($this->record['modified'] == '1') {
            return true;
            //legacy before 2.7
        }
        $cacheBucket = FileStore::fileStoreWithName('other');
        if ($this->record['modified']) {
            $filename = '/' . $this->record['id'] . '_mod.' . $this->extension;
            $stat = $cacheBucket->listFiles($filename);
            if (!count($stat)) {
                return true;
            }
        }
        $sizes = array();
        $sizes[] = Preferences::valueForModuleWithKey('CameraLife', 'thumbsize');
        $sizes[] = Preferences::valueForModuleWithKey('CameraLife', 'scaledsize');
        $options = Preferences::valueForModuleWithKey('CameraLife', 'optionsizes');
        preg_match_all('/[0-9]+/', $options, $matches);
        $sizes = array_merge($sizes, $matches[0]);

        foreach ($sizes as $cursize) {
            $filename = '/' . $this->record['id'] . '_' . $cursize . '.' . $this->extension;
            $stat = $cacheBucket->listFiles($filename);
            if (!count($stat)) {
                return true;
            }
        }
        return false;
    }

    public function getFolder()
    {
        return new Folder($this->record['path']);
    }

    public function getEXIF()
    {
        global $cameralife;

        $this->EXIF = array();
        $query = Database::select('exif', '*', "photoid=" . $this->record['id']);

        while ($row = $query->fetchAssoc()) {
            if ($row['tag'] == 'empty') {
                continue;
            }
            $this->EXIF[$row['tag']] = $row['value'];
        }

        return $this->EXIF;
    }

    private function loadEXIF($file)
    {
        $exif = exif_read_data($file, 'IFD0', true);
        if ($exif === false) {
            return;
        }
        $this->EXIF = array();
        $focallength = $exposuretime = null;
        if (isset($exif['EXIF']['DateTimeOriginal'])) {
            $this->EXIF["Date taken"] = $exif['EXIF']['DateTimeOriginal'];
            $exifPieces = explode(" ", $this->EXIF["Date taken"]);
            if (count($exifPieces) == 2) {
                $this->record['created'] = date(
                    "Y-m-d",
                    strtotime(str_replace(":", "-", $exifPieces[0]) . " " . $exifPieces[1])
                );
            }
        }
        if (isset($exif['IFD0']['Model'])) {
            $this->EXIF["Camera Model"] = $exif['IFD0']['Model'];
        }
        if (isset($exif['COMPUTED']['ApertureFNumber'])) {
            $this->EXIF["Aperture"] = $exif['COMPUTED']['ApertureFNumber'];
        }
        if (isset($exif['EXIF']['ExposureTime'])) {
            $this->EXIF["Speed"] = $exif['EXIF']['ExposureTime'];
        }
        if (isset($exif['EXIF']['ISOSpeedRatings'])) {
            $this->EXIF["ISO"] = $exif['EXIF']['ISOSpeedRatings'];
        }
        if (isset($exif['EXIF']['FocalLength'])) {
            if (preg_match('#([0-9]+)/([0-9]+)#', $exif['EXIF']['FocalLength'], $regs)) {
                $focallength = $regs[1] / $regs[2];
            }
            $this->EXIF["Focal distance"] = "${focallength}mm";
        }
        if (isset($exif['EXIF']['FocalLength'])) {
            $ccd = 35;
            if (isset($exif['COMPUTED']['CCDWidth'])) {
                $ccd = str_replace('mm', '', $exif['COMPUTED']['CCDWidth']);
            }
            $fov = round(2 * rad2deg(atan($ccd / 2 / $focallength)), 2);
            //@link http://www.rags-int-inc.com/PhotoTechStuff/Lens101/
            $this->EXIF["Field of view"] = "${fov}&deg; horizontal";
        }
        if ($focallength && $exposuretime) {
            $iso = isset($this->EXIF["ISO"]) ? $this->EXIF["ISO"] : 100;
            if ($exif['EXIF']['Flash'] % 2 == 1) {
                $light = 'Flash';
            } else {
                preg_match('#([0-9]+)/([0-9]+)#', $exposuretime, $regs);
                $exposuretime = $regs[1] / $regs[2];

                $electronVolts = pow(str_replace('f/', '', $fnumber), 2) / $iso?:100 / $exposuretime;
                $light = $electronVolts > 10 ? 'Probably outdoors' : 'Probably indoors';
            }
            $this->EXIF["Lighting"] = $light;
        }
        if (isset($exif['IFD0']['Orientation'])) {
            $this->EXIF["Orientation"] = $exif['IFD0']['Orientation'];
        }
        if (isset($exif['GPS']) && isset($exif['GPS']['GPSLatitude']) && isset($exif['GPS']['GPSLongitude'])) {
            $lat = 0;
            if (count($exif['GPS']['GPSLatitude']) > 0) {
                $lat += $this->gpsToNumber($exif['GPS']['GPSLatitude'][0]);
            }
            if (count($exif['GPS']['GPSLatitude']) > 1) {
                $lat += $this->gpsToNumber($exif['GPS']['GPSLatitude'][1]) / 60;
            }
            if (count($exif['GPS']['GPSLatitude']) > 2) {
                $lat += $this->gpsToNumber($exif['GPS']['GPSLatitude'][2]) / 3600;
            }

            $lon = 0;
            if (count($exif['GPS']['GPSLongitude']) > 0) {
                $lon += $this->gpsToNumber($exif['GPS']['GPSLongitude'][0]);
            }
            if (count($exif['GPS']['GPSLongitude']) > 1) {
                $lon += $this->gpsToNumber($exif['GPS']['GPSLongitude'][1]) / 60;
            }
            if (count($exif['GPS']['GPSLongitude']) > 2) {
                $lon += $this->gpsToNumber($exif['GPS']['GPSLongitude'][2]) / 3600;
            }

            if ($exif['GPS']['GPSLatitudeRef'] == 'S') {
                $lat *= -1;
            }
            if ($exif['GPS']['GPSLongitudeRef'] == 'W') {
                $lon *= -1;
            }

            if ($lat != 0 && $lon != 0) {
                $this->EXIF["Location"] = sprintf("%.6f, %.6f", $lat, $lon);
            }
        }

        if (!count($this->EXIF)) {
            $this->EXIF = array('empty' => 'true');
        }

        Database::delete('exif', 'photoid=' . $this->record['id']);
        foreach ($this->EXIF as $tag => $value) {
            Database::insert(
                'exif',
                array('photoid' => $this->record['id'], 'tag' => $tag, 'value' => $value)
            );
        }
    }

    /**
     * getRelated function sets this->context
     *
     * @access public
     * @return array - set of views that contain this photo
     */
    public function getRelated()
    {
        //todo pass in referrer
        global $_SERVER;
        $retval = array($this->getFolder());
        $this->context = $this->getFolder();

        // Given no better information, best context is this photo's path
        if (!isset($_SERVER['HTTP_REFERER'])) {
            return $retval;
        }

        // Find if the referer is an album
        if (preg_match("/album/", $_SERVER['HTTP_REFERER'], $regs)) {
            if (isset($_SERVER['HTTP_REFERER'])
                && (preg_match("#album.php\?id=([0-9]*)#", $_SERVER['HTTP_REFERER'], $regs) || preg_match(
                    "#albums/([0-9]+)#",
                    $_SERVER['HTTP_REFERER'],
                    $regs
                ))
            ) {
                $album = new Album($regs[1]);
                $retval[] = $album;
                $this->context = $album;
            }
        }

        // Find all albums that contain this photo, this is not 100%
        $result = Database::select(
            'albums',
            'id,name',
            "'" . addslashes($this->get('description')) . "' LIKE CONCAT('%',term,'%')"
        );
        while ($albumrecord = $result->fetchAssoc()) {
            if (($this->context instanceof Album) && $this->context->get('id') == $albumrecord['id']) {
                continue;
            }
            $album = new Tag($albumrecord['id']);
            $retval[] = $album;
        }

        // Did they come from a search??
        if (preg_match("#q=([^&]*)#", $_SERVER['HTTP_REFERER'], $regs)) {
            $search = new Search($regs[1]);
            $retval[] = $search;
            $this->context = $search;
        } else {
            // Find all photos named exactly like this
            $search = new Search($this->get('description'));
            if ($search->getPhotoCount() > 1) {
                $retval[] = $search;
            }
        }
        return $retval;
    }

    /**
     * Convert "2/4" to 0.5 and "4" to 4
     * @access private
     */
    private function gpsToNumber($num)
    {
        $parts = explode('/', $num);
        if (count($parts) == 0) {
            return 0;
        }
        if (count($parts) == 1) {
            return $parts[0];
        }
        return floatval($parts[0]) / floatval($parts[1]);
    }

    public function getLikeCount()
    {
        global $cameralife;
        $ratings = Database::selectOne(
            'ratings',
            'COUNT(rating)',
            'id=' . $this->get('id') . ' AND rating > 0'
        );
        return $ratings;
    }

    public function getContext()
    {
        if (!$this->context) {
            $this->getRelated();
        }

        if (!count($this->contextPhotos)) {
            $this->context->SetPage(0, 99);
            $this->contextPhotos = $this->context->getPhotos(); /* Using the base class, how hot is that? */
            $last = null;
            foreach ($this->contextPhotos as $cur) {
                if ($cur->get('id') == $this->get('id') && $last) {
                    $this->contextPrev = $last;
                }
                if ($last && $last->get('id') == $this->get('id')) {
                    $this->contextNext = $cur;
                }
                $last = $cur;
            }

        }

        return $this->contextPhotos;
    }

    public function getPrevious()
    {
        if (!count($this->contextPhotos)) {
            $this->getContext();
        }

        return $this->contextPrev;
    }

    // returns the next photo or false if none exists
    public function getNext()
    {
        if (!count($this->contextPhotos)) {
            $this->getContext();
        }

        return $this->contextNext;
    }

    ///////////////////////////////////////////////////

    public function favoriteByUser(User $user)
    {
        //todo set RECEIPT in the user session
        $condition = 'id = ' . $this->record['id'] . ' AND ';
        if ($user->isLoggedIn) {
            $condition .= 'username = "' . $user->name . '"';
        } else {
            $condition .= 'user_ip = "' . $user->remoteAddr . '"';
        }
        Database::delete('ratings', $condition);
        Database::insert('ratings', ['id'=>$this->record['id'], 'username'=>$user->name, 'user_ip'=>$user->remoteAddr, 'date'=>date('Y-M-D H:i:s'), 'rating'=>5]);
    }

    public function unfavoriteByUser(User $user)
    {
        //todo set RECEIPT in the user session
        $condition = 'id = ' . $this->record['id'] . ' AND ';
        if ($user->isLoggedIn) {
            $condition .= 'username = "' . $user->name . '"';
        } else {
            $condition .= 'user_ip = "' . $user->remoteAddr . '"';
        }
        Database::delete('ratings', $condition);
    }


}
