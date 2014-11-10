<?php
namespace CameraLife;

/**
 * Class Photo provides a front end to working with photos
 *
 * @author    William Entriken <cameralife@phor.net>
 * @access    public
 * @version
 * @copyright 2001-2009 William Entriken
 */
class Photo extends View
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
        $retval = Photo::getPhotoWithRecord(array_merge($defaults, $record));
        $retval->record['id'] = $cameralife->database->Insert('photos', $retval->record);
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
        $result = $cameralife->database->Select('photos', '*', "filename=:f AND path=:p", null, null, $bind);
        $record = $result->fetchAssoc()
        or $cameralife->error("Photo not found at path");
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
        global $cameralife;
        $bind = array('i'=>$id);
        $result = $cameralife->database->Select('photos', '*', "id=:i", null, null, $bind);
        $record = $result->fetchAssoc()
        or $cameralife->error("Photo #$id not found");
        return Photo::getPhotoWithRecord($record);
    }

    /**
     * To get an empty Photo pass nothing ie NULL
     * To load a photo pass a photo ID
     * To create a photo pass an array
     * <b>Required fields <var>filename</var>, <var>path</var>, <var>username</var></b>
     * Optional fields <var>status</var>, <var>description</var>, <var>fsize</var>, <var>created</var>
     */
    private function __construct($original = null)
    {
        global $cameralife;
        $this->context = false;
        $this->contextPrev = false;
        $this->contextNext = false;
        $this->contextPhotos = array();
        $this->EXIF = array();
    }
    
    //////////////////////////////////////////////////////////

    public static function photoExists($original)
    {
        global $cameralife;

        if (!is_numeric($original)) {
            $cameralife->error("Input needs to be a number");
        }

        $result = $cameralife->database->Select('photos', '*', "id=$original");
        $a = $result->fetchAssoc();

        return $a != 0;
    }

    public function set($key, $value)
    {
        global $cameralife;

        $receipt = null;
        if ($key != 'hits' && $key != 'filename' && $key != 'path') {
            $receipt = AuditTrail::createAuditTrailForChange('photo', $this->record['id'], $key, $this->record[$key], $value);
        }
        /*
        ///TODO: if status is changed, update permissions in file store
        ///TODO: also update _mod and _thumbnails
        if ($key == 'status') {
            $fullpath = rtrim('/' . ltrim($this->record['path'], '/'), '/') . '/' . $this->record['filename'];
            $cameralife->fileStore->setPermissions('photo', $fullpath, $value!=0);
        }
        */
        $this->record[$key] = $value;
        $cameralife->database->Update('photos', array($key => $value), 'id=' . $this->record['id']);

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
        list ($file, $temp, $this->record['mtime']) = $cameralife->fileStore->GetFile('photo', $fullpath);
        $this->record['fsize'] = filesize($file);
        $this->record['created'] = date('Y-m-d', $this->record['mtime']);
        $this->loadEXIF($file);

        $this->image = $cameralife->imageProcessing->createImage($file)
        or $cameralife->error("Bad photo load: $file");
        if (!$this->image->Check()) {
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
        global $cameralife;

        $this->loadImage(); // sets $this->EXIF and $this-record
        if (($cameralife->getPref('autorotate') == 'yes')
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
            
            $tempfile = tempnam($cameralife->getPref('tempdir'), 'cameralife_mod');
            $activeImage->save($tempfile);
            $filename = '/' . $this->record['id'] . '_mod.' . $this->extension;
            $cameralife->fileStore->PutFile('other', $filename, $tempfile, $this->record['status'] != 0);
        }
                
        $imagesize = $activeImage->GetSize();
        $this->record['width'] = $imagesize[0];
        $this->record['height'] = $imagesize[1];
        
        $sizes = array($cameralife->getPref('thumbsize'), $cameralife->getPref('scaledsize'));
        preg_match_all('/[0-9]+/', $cameralife->getPref('optionsizes'), $matches);
        $sizes = array_merge($sizes, $matches[0]);
        rsort($sizes);
        $files = array();

        foreach ($sizes as $cursize) {
            $tempfile = tempnam($cameralife->getPref('tempdir'), 'cameralife_' . $cursize);
            $dims = $activeImage->Resize($tempfile, $cursize);
            $filename = '/' . $this->record['id'] . '_' . $cursize . '.' . $this->extension;
            $cameralife->fileStore->PutFile('other', $filename, $tempfile, $this->record['status'] != 0);
            @unlink($file);
            if ($cursize == $cameralife->getPref('thumbsize')) {
                $this->record['tn_width'] = $dims[0];
                $this->record['tn_height'] = $dims[1];
            }
        }

        $cameralife->database->Update('photos', $this->record, 'id=' . $this->record['id']);
    }

    private function deleteThumbnails()
    {
        global $cameralife;
        @$cameralife->fileStore->EraseFile('other', '/' . $this->record['id'] . '_mod.' . $this->extension);
        @$cameralife->fileStore->EraseFile(
            'other',
            '/' . $this->record['id'] . '_' . $cameralife->getPref('scaledsize') . '.' . $this->extension
        );
        @$cameralife->fileStore->EraseFile(
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
        global $cameralife;

        $url = null;
        if ($format == 'photo' || $format == '') {
            if ($this->get('modified')) {
                $url = $cameralife->fileStore->GetURL('other', '/' . $this->get('id') . '_mod.' . $this->extension);
            } else {
                $url = $cameralife->fileStore->getURL('photos', '/' . $this->get('path') . $this->get('filename'));
            }
        } elseif ($format == 'scaled') {
            $url = $cameralife->fileStore->getURL(
                'other',
                '/' . $this->get('id') . '_' . $cameralife->getPref('scaledsize') . '.' . $this->extension
            );
        } elseif ($format == 'thumbnail') {
            $url = $cameralife->fileStore->GetURL(
                'other',
                '/' . $this->get('id') . '_' . $cameralife->getPref('thumbsize') . '.' . $this->extension
            );
        } elseif (is_numeric($format)) {
            $valid = preg_split('/[, ]+/', $cameralife->getPref('optionsizes'));
            if (in_array($format, $valid)) {
                $url = $cameralife->fileStore->GetURL(
                    'other',
                    '/' . $this->get('id') . '_' . $format . '.' . $this->extension
                );
            } else {
                $cameralife->error('This image size has not been allowed');
            }
        } else {
            $cameralife->error('Bad format parameter');
        }

        if ($url) {
            return $url;
        }

        if ($cameralife->getPref('rewrite') == 'yes') {
            return $cameralife->baseURL . "/photos/" . $this->record['id'] . '.' . $this->extension . '?' . 'scale=' . $format . '&' . 'ver=' . ($this->record['mtime'] + 0);
        } else {
            return $cameralife->baseURL . '/media.php?id=' . $this->record['id'] . "&size=$format&ver=" . ($this->record['mtime'] + 0);
        }
    }

    public function getFolder()
    {
        return new Folder($this->record['path'], false);
    }

    public function getEXIF()
    {
        global $cameralife;

        $this->EXIF = array();
        $query = $cameralife->database->Select('exif', '*', "photoid=" . $this->record['id']);

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
        global $cameralife;

        $exif = @exif_read_data($file, 'IFD0', true);
        $this->EXIF = array();
        if ($exif === false) {
            return;
        } else {
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
                if (!$iso) {
                    $iso = 100;
                }
                if ($exif['EXIF']['Flash'] % 2 == 1) {
                    $light = 'Flash';
                } else {
                    if (preg_match('#([0-9]+)/([0-9]+)#', $exposuretime, $regs)) {
                        ;
                    }
                    $exposuretime = $regs[1] / $regs[2];

                    $ev = pow(str_replace('f/', '', $fnumber), 2) / $iso / $exposuretime;
                    if ($ev > 10) {
                        $light = 'Probably outdoors';
                    } else {
                        $light = 'Probably indoors';
                    }
                }
                $this->EXIF["Lighting"] = $light;
            }
            if (isset($exif['IFD0']['Orientation'])) {
                $this->EXIF["Orientation"] = $exif['IFD0']['Orientation'];
            }
            if (isset($exif['GPS']) && isset($exif['GPS']['GPSLatitude']) && $exif['GPS']['GPSLongitude']) {
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
        }

        if (!count($this->EXIF)) {
            $this->EXIF = array('empty' => 'true');
        }

        $cameralife->database->Delete('exif', 'photoid=' . $this->record['id']);
        foreach ($this->EXIF as $tag => $value) {
            $cameralife->database->Insert(
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
        global $_SERVER, $cameralife;
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
        $result = $cameralife->database->Select(
            'albums',
            'id,name',
            "'" . addslashes($this->get('description')) . "' LIKE CONCAT('%',term,'%')"
        );
        while ($albumrecord = $result->fetchAssoc()) {
            if (($this->context instanceof Album) && $this->context->get('id') == $albumrecord['id']) {
                continue;
            }
            $album = new Album($albumrecord['id']);
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
        $ratings = $cameralife->database->SelectOne(
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

    public function getOpenGraph()
    {
        global $cameralife;
        $retval = array();
        $retval['og:title'] = $this->record['description'];
        $retval['og:type'] = 'website';
        $retval['og:url'] = $cameralife->baseURL . '/photos/' . $this->record['id'];
        if ($cameralife->getPref('rewrite') == 'no') {
            $retval['og:url'] = $cameralife->baseURL . '/photo.php?id=' . $this->record['id'];
        }
        $retval['og:image'] = $this->getMediaURL('thumbnail');
        $retval['og:image:type'] = 'image/jpeg';
        $retval['og:image:width'] = $this->record['tn_width'];
        $retval['og:image:height'] = $this->record['tn_height'];

        return $retval;
    }
}
