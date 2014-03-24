<?php
/**
 * Class Photo provides a front end to working with photos
 * @author Will Entriken <cameralife@phor.net>
 * @access public
 * @version
 * @copyright Copyright (c) 2001-2009 Will Entriken
 */
class Photo extends View
{
  public $record, $image;

  /**
   * The file extension, e.g. png
   *
   * @var String
   * @access public
   */
  public $extension;

  /**
   * context
   * an Album, Search or Folder of where the user came from to get to this photo
   *
   * @var mixed
   * @access private
   */
  private $context;

  /**
   * contextPhotos
   * An ordered set of photos in the same context as this one
   *
   * @var mixed
   * @access private
   */
  private $contextPhotos;

  /**
   * contextPrev
   * the previous photo in context
   *
   * @var mixed
   * @access private
   */
  private $contextPrev;

  /**
   * contextNext
   * the next photo in contex
   *
   * @var mixed
   * @access private
   */
  private $contextNext;

  /**
  * To get an empty Photo pass nothing ie NULL
  * To load a photo pass a photo ID
  * To create a photo pass an array
  * <b>Required fields <var>filename</var>, <var>path</var>, <var>username</var></b>
  * Optional fields <var>status</var>, <var>description</var>, <var>fsize</var>, <var>created</var>
  */
  public function __construct($original = NULL)
  {
    global $cameralife;
    parent::__construct();

    if (is_null($original)) {
      $this->record['id'] = NULL;
    } elseif (is_numeric($original)) { # This is an ID
      $result = $cameralife->database->Select('photos','*', "id=$original");
      $this->record = $result->FetchAssoc()
        or $cameralife->error("Photo #$original not found", __FILE__, __LINE__);
    } elseif (is_array($original)) { # A new image, given by an array
      $this->record['description'] = 'unnamed';

//      if (!preg_match('/^dscn/i', $this->record['filename']) &&
//        !preg_match('/^im/i', $this->record['filename'])) // useless filename
//        $this->record['description'] = preg_replace('/.[^.]+$/', '', ucwords($photo->get('filename')));

      $this->record['status'] = '0';
      $this->record['created'] = date('Y-m-d');
      $this->record['modified'] = '0';
      $this->record['mtime'] = '0';
      $this->record = array_merge($this->record, $original);

      $this->record['id'] = $cameralife->database->Insert('photos', $this->record);
    }
    $this->context = false;
    $this->contextPrev = false;
    $this->contextNext = false;
    $this->contextPhotos = array();
    $this->EXIF = array();

    if(isset($this->record['filename']))
      $path_parts = pathinfo($this->record['filename']);
    if(isset($path_parts['extension']))
      $this->extension = strtolower($path_parts['extension']);
  }

  public static function photoExists($original)
  {
    global $cameralife;

    if(!is_numeric($original))
      $cameralife->error("Input needs to be a number", __FILE__, __LINE__);

    $result = $cameralife->database->Select('photos','*', "id=$original");
    $a = $result->FetchAssoc();

    return $a != 0;
  }

  public function set($key, $value)
  {
    global $cameralife;

    $receipt = NULL;
    if ($key != 'hits')
      $receipt = AuditTrail::log('photo',$this->record['id'],$key,$this->record[$key],$value);
    if ($key == 'status') {
      $fullpath = rtrim('/'.ltrim($this->record['path'],'/'),'/').'/'.$this->record['filename'];
//      $cameralife->fileStore->setPermissions('photo', $fullpath, $value!=0);
//TODO: also set for _mod and _ thumbnails
    }
    $this->record[$key] = $value;
    $cameralife->database->Update('photos', array($key=>$value), 'id='.$this->record['id']);

    return $receipt;
  }

  public function get($key)
  {
    if (isset($this->record[$key]))
      return $this->record[$key];
    else
      return null;
  }

  /// Initialize the <var>$this->image</var> variable and collect fsize and $this->loadEXIF if possible
  public function loadImage($onlyWantEXIF = false)
  {
    global $cameralife;

    if (isset($this->image)) return;
    $fullpath = rtrim('/'.ltrim($this->record['path'],'/'),'/').'/'.$this->record['filename'];
    list ($file, $temp, $this->record['mtime']) = $cameralife->fileStore->GetFile('photo',$fullpath);
    if (is_null($this->record['modified']) || $this->record['modified'] == 0 || $this->record['modified'] == '') {
      $this->record['fsize'] = filesize($file);
      $this->record['created'] = date('Y-m-d', $this->record['mtime']);
      $this->loadEXIF($file);
    }

    if (!$onlyWantEXIF) {
      $this->image = $cameralife->ImageProcessing->CreateImage($file)
        or $cameralife->error("Bad photo load: $file",__FILE__,__LINE__);
      if (!$this->image->Check()) $cameralife->error("Bad photo processing: $file",__FILE__,__LINE__);
    }
    if ($temp) unlink($file);
  }

  /// Scale image to all needed sizes and save in file store, update image/tn sizes
  /// also update fsize if this is unmodified.
  public function generateThumbnail()
  {
    global $cameralife;

    $this->loadImage(); // sets $this->EXIF and $this-record
    if (($cameralife->getPref('autorotate') == 'yes') && ($this->record['modified'] == NULL || $this->record['modified'] == 0)) {
      if ($this->EXIF['Orientation'] == 3) {
        $this->rotate(180);
      } elseif ($this->EXIF['Orientation'] == 6) {
        $this->rotate(90);
      } elseif ($this->EXIF['Orientation'] == 8) {
        $this->rotate(270);
      }
    }
    $imagesize = $this->image->GetSize();

    preg_match_all('/[0-9]+/',$cameralife->getPref('optionsizes'), $sizes);
    $sizes = $sizes[0];
    if ($sizes == "") $sizes = array();
    $sizes[] = $cameralife->getPref('thumbsize');
    $sizes[] = $cameralife->getPref('scaledsize');
    $files = array();
    rsort($sizes);

    foreach ($sizes as $cursize) {
      $tempfile = tempnam($cameralife->getPref('tempdir'), 'cameralife_'.$cursize);
      $dims = $this->image->Resize($tempfile, $cursize);
      $files[$cursize] = $tempfile;
      if ($cursize == $cameralife->getPref('thumbsize'))
        $thumbsize = $dims;
    }

    $fullpath = rtrim('/'.ltrim($this->record['path'],'/'),'/').'/'.$this->record['filename'];
    foreach ($files as $size=>$file) {
      $cameralife->fileStore->PutFile('other', '/'.$this->record['id'].'_'.$size.'.'.$this->extension, $file, $this->record['status']!=0);
      @unlink($file);
    }

    $this->record['width'] = $imagesize[0];
    $this->record['height'] = $imagesize[1];
    $this->record['tn_width'] = $thumbsize[0];
    $this->record['tn_height'] = $thumbsize[1];

    $cameralife->database->Update('photos',$this->record,'id='.$this->record['id']);
  }

  public function rotate($angle)
  {
    global $cameralife;

    $this->loadImage();
    $this->image->Rotate($angle);

    $temp = tempnam($cameralife->getPref('tempdir'), 'cameralife_');
    $this->image->Save($temp);
//TODO: unlink old thumbnails
    $this->record['mtime'] = time();

    $this->record['modified'] = 1;
    $cameralife->database->Update('photos',$this->record,'id='.$this->record['id']);
  }

  public function revert()
  {
    global $cameralife;

    if ($this->record['modified']) {
      $this->record['modified'] = 0;
      $cameralife->fileStore->EraseFile('other','/'.$this->record['id'].'_mod.'.$this->extension);
      $cameralife->fileStore->EraseFile('other','/'.$this->record['id'].'_'.$cameralife->getPref('scaledsize').'.'.$this->extension);
      $cameralife->fileStore->EraseFile('other','/'.$this->record['id'].'_'.$cameralife->getPref('scaledsize').'.'.$this->extension);
      $this->record['mtime'] = 0;
    }
    $cameralife->database->Update('photos',$this->record,'id='.$this->record['id']);
  }

  public function erase()
  {
    global $cameralife;
    $this->set('status', 9);
    /*
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
    if ($this->image)
      $this->image->Destroy();
  }

  public function getMediaURL($format='thumbnail')
  {
    global $cameralife;

    $url = NULL;
    if ($format == 'photo' || $format == '') {
      if ($this->get('modified'))
        $url = $cameralife->fileStore->GetURL('other', '/'.$this->get('id').'_mod.'.$this->extension);
      else
        $url = $cameralife->fileStore->getURL('photos', '/'.$this->get('path').$this->get('filename'));
    }
    elseif ($format == 'scaled')
      $url = $cameralife->fileStore->getURL('other', '/'.$this->get('id').'_'.$cameralife->getPref('scaledsize').'.'.$this->extension);
    elseif ($format == 'thumbnail')
      $url = $cameralife->fileStore->GetURL('other', '/'.$this->get('id').'_'.$cameralife->getPref('thumbsize').'.'.$this->extension);
    elseif (is_numeric($format)) {
      $valid = preg_split('/[, ]+/',$cameralife->getPref('optionsizes'));
      if (in_array($format, $valid))
        $url = $cameralife->fileStore->GetURL('other', '/'.$this->get('id').'_'.$format.'.'.$this->extension);
      else
        $cameralife->error('This image size has not been allowed');
    } 
    else
      $cameralife->error('Bad format parameter');
    
    if ($url)
      return $url;

    if ($cameralife->getPref('rewrite') == 'yes')
      return $cameralife->baseURL."/photos/".$this->record['id'].'.'.$this->extension.'?'.'scale='.$format.'&'.'ver='.($this->record['mtime']+0);
    else
      return $cameralife->baseURL.'/media.php?id='.$this->record['id']."&size=$format&ver=".($this->record['mtime']+0);
  }
  /// DEPRECATED
  public function getMedia($size='thumbnail')
  {
    return htmlentities($this->getMediaURL($size));
  }

  public function getFolder()
  {
    return new Folder($this->record['path'], FALSE);
  }

  public function getEXIF()
  {
    global $cameralife;

    $this->EXIF = array();
    $query = $cameralife->database->Select('exif', '*', "photoid=".$this->record['id']);

    while ($row = $query->FetchAssoc()) {
      if ($row['tag'] == 'empty') continue;
      $this->EXIF[$row['tag']] = $row['value'];
    }

    return $this->EXIF;
  }

  public function loadEXIF($file)
  {
    global $cameralife;

    $exif = @exif_read_data($file, 'IFD0', true);
    $this->EXIF = array();
    if ($exif===false)
      return $retval;
    else {
      $focallength = $exposuretime = NULL;
      if (isset($exif['EXIF']['DateTimeOriginal'])) {
        $this->EXIF["Date taken"]=$exif['EXIF']['DateTimeOriginal'];
        $exifPieces = explode(" ", $this->EXIF["Date taken"]);
        $this->record['created'] = date("Y-m-d",strtotime(str_replace(":","-",$exifPieces[0])." ".$exifPieces[1]));
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
        if(preg_match('#([0-9]+)/([0-9]+)#', $exif['EXIF']['FocalLength'], $regs))
        $focallength = $regs[1] / $regs[2];
        $this->EXIF["Focal distance"]="${focallength}mm";
      }
      if (isset($exif['EXIF']['FocalLength'])) {
        $ccd = 35;
        if (isset($exif['COMPUTED']['CCDWidth']))
          $ccd = str_replace('mm','',$exif['COMPUTED']['CCDWidth']);
        $fov = round(2*rad2deg(atan($ccd/2/$focallength)),2);
        //@link http://www.rags-int-inc.com/PhotoTechStuff/Lens101/

        $this->EXIF["Field of view"]="${fov}&deg; horizontal";
      }
      if ($focallength && $exposuretime) {
        if (!$iso) $iso = 100;
        if ($exif['EXIF']['Flash'] % 2 == 1)
        $light = 'Flash';
        else {
          if (preg_match('#([0-9]+)/([0-9]+)#', $exposuretime, $regs));
            $exposuretime = $regs[1] / $regs[2];

          $ev = pow(str_replace('f/','',$fnumber),2) / $iso / $exposuretime;
          if ($ev > 10)
            $light = 'Probably outdoors';
          else
            $light = 'Probably indoors';
        }
        $this->EXIF["Lighting"]=$light;
      }
      if ($orient = $exif['IFD0']['Orientation']) {
        $this->EXIF["Orientation"]=$orient;
      }
      if (isset($exif['GPS']) && isset($exif['GPS']['GPSLatitude']) && $exif['GPS']['GPSLongitude']) {
        $lat = 0;
        if (count($exif['GPS']['GPSLatitude']) > 0)
          $lat += $this->GPS2num($exif['GPS']['GPSLatitude'][0]);
        if (count($exif['GPS']['GPSLatitude']) > 1)
          $lat += $this->GPS2num($exif['GPS']['GPSLatitude'][1]) / 60;
        if (count($exif['GPS']['GPSLatitude']) > 2)
          $lat += $this->GPS2num($exif['GPS']['GPSLatitude'][2]) / 3600;

        $lon = 0;
        if (count($exif['GPS']['GPSLongitude']) > 0)
          $lon += $this->GPS2num($exif['GPS']['GPSLongitude'][0]);
        if (count($exif['GPS']['GPSLongitude']) > 1)
          $lon += $this->GPS2num($exif['GPS']['GPSLongitude'][1]) / 60;
        if (count($exif['GPS']['GPSLongitude']) > 2)
          $lon += $this->GPS2num($exif['GPS']['GPSLongitude'][2]) / 3600;

        if ($exif['GPS']['GPSLatitudeRef'] == 'S')
          $lat *= -1;
        if ($exif['GPS']['GPSLongitudeRef'] == 'W')
          $lon *= -1;

        if ($lat != 0 && $lon != 0)
          $this->EXIF["Location"] = sprintf("%.6f, %.6f",$lat, $lon);
      }
    }

    if (!count($this->EXIF)) $this->EXIF=array('empty'=>'true');

    $cameralife->database->Delete('exif', 'photoid='.$this->record['id']);
    foreach ($this->EXIF as $tag=>$value)
      $cameralife->database->Insert('exif', array('photoid'=>$this->record['id'], 'tag'=>$tag, 'value'=>$value));
  }

  /**
   * getRelated function
   * 
   * @access public
   * @return array - set of views that contain this photo
   */
  public function getRelated()
  {
    global $_SERVER, $cameralife;

    $retval = array();
    $this->context = false;

    if (isset($_SERVER['HTTP_REFERER']) &&
        preg_match("/start=([0-9]*)/",$_SERVER['HTTP_REFERER'],$regs))
      $extrasearch = "&amp;start=".$regs[1];

    // Find if the referer is an album
    if (isset($_SERVER['HTTP_REFERER']) &&
        (preg_match("#album.php\?id=([0-9]*)#",$_SERVER['HTTP_REFERER'],$regs) || preg_match("#albums/([0-9]+)#",$_SERVER['HTTP_REFERER'],$regs)))
    {
      $album = new Album($regs[1]);
      $retval[] = $album;
      $this->context = $album;
    }

    // Find all albums that contain this photo, this is not 100%
    $result = $cameralife->database->Select('albums','id,name',"'".addslashes($this->get('description'))."' LIKE CONCAT('%',term,'%')");
    while ($albumrecord = $result->FetchAssoc()) {
      if (($this->context instanceof Album) && $this->context->get('id') == $albumrecord['id']) // PHP5
        continue;
      $album = new Album($albumrecord['id']);
      $retval[] = $album;
    }

    // Did they come from a search??
    if (isset($_SERVER['HTTP_REFERER']) &&
        preg_match("#q=([^&]*)#",$_SERVER['HTTP_REFERER'],$regs))
    {
      $search = new Search($regs[1]);
      $retval[] = $search;
      $this->context = $search;
    } else {
      // Find all photos named exactly like this
      $search = new Search($this->get('description'));
      $counts = $search->getCounts();
      if ($counts['photos'] > 1) {
        $retval[] = $search;
      }
    }

    if (strlen($this->get('path')) > 0) {
      $folder = $this->getFolder();
      $retval[] = $folder;
      if (!$this->context) {
        $this->context = $folder;
      }
    }

    return $retval;
  }

  /**
   * Convert "2/4" to 0.5 and "4" to 4
   * @access private
   */
  private function GPS2num($num)
  {
    $parts = explode('/', $num);
    if(count($parts) == 0)

      return 0;
    if(count($parts) == 1)

      return $parts[0];
    return floatval($parts[0]) / floatval($parts[1]);
  }

  public function getContext()
  {
    if (!$this->context)
      $this->getRelated();

    if (!count($this->contextPhotos)) {
      $this->context->SetPage(0,99);

      $this->contextPhotos = $this->context->GetPhotos(); /* Using the base class, how hot is that? */
      $last = new Photo();
      foreach ($this->contextPhotos as $cur) {
        if ($cur->Get('id') == $this->get('id') && $last->get('id'))
          $this->contextPrev = $last;
        if ($last->get('id') == $this->get('id'))
          $this->contextNext = $cur;
        $last = $cur;
      }

    }

    return $this->contextPhotos;
  }

  public function getPrevious()
  {
    if (!count($this->contextPhotos))
      $this->getContext();
    return $this->contextPrev;
  }

  // returns the next photo or false if none exists
  public function getNext()
  {
    if (!count($this->contextPhotos))
      $this->getContext();
    return $this->contextNext;
  }

  public function getOpenGraph()
  {
    global $cameralife;
    $retval = array();
    $retval['og:title'] = $this->record['description'];
    $retval['og:type'] = 'website';
    $retval['og:url'] = $cameralife->baseURL.'/photos/'.$this->record['id'];
    if ($cameralife->getPref('rewrite') == 'no')
      $retval['og:url'] = $cameralife->baseURL.'/photo.php?id='.$this->record['id'];
    $retval['og:image'] = $this->getMediaURL('thumbnail');
    $retval['og:image:type'] = 'image/jpeg';
    $retval['og:image:width'] = $this->record['tn_width'];
    $retval['og:image:height'] = $this->record['tn_height'];

    return $retval;
  }
}
