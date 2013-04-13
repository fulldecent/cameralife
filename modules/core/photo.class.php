<?php
/**
 * Class Photo provides a front end to working with photos
 * @author Will Entriken <cameralife@phor.net>
 * @access public
 * @version
 * @copyright Copyright (c) 2001-2009 Will Entriken
 */

/**
 * This class is for getting and using photos
 * @var mixed $context an Album, Search or Folder from where the user retrieved the photo
 * @var mixed $contextPhotos the photos from the 'context', that is an Album ,a Folder or a Search result
 * @var mixed $contextPrev the previous photo in the 'context'
 * @var mixed $contextNext the next photo in the 'context'
 */
class Photo extends View
{
  public $record, $image;

  public $context; // an Album, Search or Folder of where the user came from to get to this photo
  public $contextPhotos; // photos from context
  public $contextPrev; // the previous photo in context
  public $contextNext; // the next photo in contex
  public $extension;

  /**
  * To get an empty Photo pass nothing ie NULL
  * To load a photo pass a photo ID
  * To create a photo pass an array
  * <b>Required fields <var>filename</var>, <var>path</var>, <var>username</var></b>
  * Optional fields <var>status</var>, <var>description</var>, <var>fsize</var>, <var>created</var>
  */
  public function Photo($original = NULL)
  {
    global $cameralife;

    if (is_null($original)) {
      $this->record['id'] = NULL;
    } elseif (is_numeric($original)) { # This is an ID
      $result = $cameralife->Database->Select('photos','*', "id=$original");
      $this->record = $result->FetchAssoc()
        or $cameralife->Error("Photo #$original not found", __FILE__, __LINE__);
    } elseif (is_array($original)) { # A new image, given by an array
      $this->record['description'] = 'unnamed';
      $this->record['status'] = '0';
      $this->record['created'] = date('Y-m-d');
      $this->record['modified'] = '0';
      $this->record['mtime'] = '0';
      $this->record = array_merge($this->record, $original);

      $this->record['id'] = $cameralife->Database->Insert('photos', $this->record);
      // Generate the thumbnail later, when requested
      //$this->GenerateThumbnail(); // Sets mtime, width, height, fsize
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

  public static function PhotoExists($original)
  {
    global $cameralife;

    if(!is_numeric($original))
      $cameralife->Error("Input needs to be a number", __FILE__, __LINE__);

    $result = $cameralife->Database->Select('photos','*', "id=$original");
    $a = $result->FetchAssoc();

    return $a != 0;
  }

  public function Set($key, $value)
  {
    global $cameralife;

    $receipt = NULL;
    if ($key != 'hits')
      $receipt = AuditTrail::Log('photo',$this->record['id'],$key,$this->record[$key],$value);
    if ($key == 'status')
      $cameralife->PhotoStore->SetPermissions($this);
    $this->record[$key] = $value;
    $cameralife->Database->Update('photos', array($key=>$value), 'id='.$this->record['id']);

    return $receipt;
  }

  public function Get($key)
  {
    return $this->record[$key];
  }

  /// Initialize the <var>$this->image</var> variable and collect fsize and $this->LoadEXIF if possible
  public function LoadImage($onlyWantEXIF = false)
  {
    global $cameralife;

    if (isset($this->image)) return;
    list ($file, $temp, $this->record['mtime']) = $cameralife->PhotoStore->GetFile($this);
///TODO there shouldnt be three cases here
    if (is_null($this->record['modified']) || $this->record['modified'] == 0 || $this->record['modified'] == '') {
      $this->record['fsize'] = filesize($file);
      $this->record['created'] = date('Y-m-d', $this->record['mtime']);
      $this->LoadEXIF($file);
    }

    if (!$onlyWantEXIF) {
      $this->image = $cameralife->ImageProcessing->CreateImage($file)
        or $cameralife->Error("Bad photo load: $file",__FILE__,__LINE__);
      if (!$this->image->Check()) $cameralife->Error("Bad photo processing: $file",__FILE__,__LINE__);
    }
    if ($temp) unlink($file);
  }

  /// Scale image to all needed sizes and save in photostore, update image/tn sizes
  /// also update fsize if this is unmodified.
  public function GenerateThumbnail()
  {
    global $cameralife;

    $this->LoadImage(); // sets $this->EXIF and $this-record
    if (($cameralife->GetPref('autorotate') == 'yes') && ($this->record['modified'] == NULL || $this->record['modified'] == 0)) {
      if ($this->EXIF['Orientation'] == 3) {
        $this->Rotate(180);
      } elseif ($this->EXIF['Orientation'] == 6) {
        $this->Rotate(90);
      } elseif ($this->EXIF['Orientation'] == 8) {
        $this->Rotate(270);
      }
    }
    $imagesize = $this->image->GetSize();

    preg_match_all('/[0-9]+/',$cameralife->GetPref('optionsizes'), $sizes);
    $sizes = $sizes[0];
    if ($sizes == "") $sizes = array();
    $sizes[] = $cameralife->GetPref('thumbsize');
    $sizes[] = $cameralife->GetPref('scaledsize');
    $files = array();
    rsort($sizes);

    foreach ($sizes as $cursize) {
      $tempfile = tempnam($cameralife->GetPref('tempdir'), 'cameralife_'.$cursize);
      $dims = $this->image->Resize($tempfile, $cursize);
      $files[$cursize] = $tempfile;
      if ($cursize == $cameralife->GetPref('thumbsize'))
        $thumbsize = $dims;
    }

    $cameralife->PhotoStore->PutThumbnails($this, $files);
    foreach ($files as $size=>$file)
      @unlink($file);

    $this->record['width'] = $imagesize[0];
    $this->record['height'] = $imagesize[1];
    $this->record['tn_width'] = $thumbsize[0];
    $this->record['tn_height'] = $thumbsize[1];

    $cameralife->Database->Update('photos',$this->record,'id='.$this->record['id']);
  }

  public function Rotate($angle)
  {
    global $cameralife;

    $this->LoadImage();
    $this->image->Rotate($angle);

    $temp = tempnam($cameralife->GetPref('tempdir'), 'cameralife_');
    $this->image->Save($temp);
    $cameralife->PhotoStore->ModifyFile($this, $temp); # $temp is unlinked by ModifyFile
    $this->record['mtime'] = time();

    $this->record['modified'] = 1;
    $cameralife->Database->Update('photos',$this->record,'id='.$this->record['id']);
  }

  public function Revert()
  {
    global $cameralife;

    if ($this->record['modified']) {
      $this->record['modified'] = 0;
      $cameralife->PhotoStore->ModifyFile($this, NULL);
      $this->record['mtime'] = 0;
    }

    $cameralife->Database->Update('photos',$this->record,'id='.$this->record['id']);
  }

  public function Erase()
  {
    global $cameralife;

    $cameralife->PhotoStore->EraseFile($this);
    $cameralife->Database->Delete('photos','id='.$this->record['id']);
    $cameralife->Database->Delete('logs',"record_type='photo' AND record_id=".$this->record['id']);
    $cameralife->Database->Delete('ratings',"id=".$this->record['id']);
    $cameralife->Database->Delete('comments',"photo_id=".$this->record['id']);
    $cameralife->Database->Delete('exif',"photoid=".$this->record['id']);
    $this->Destroy();

    # Bonus code
    if (file_exists($cameralife->base_dir.'/deleted.log')) {
      $fh = fopen($cameralife->base_dir.'/deleted.log', 'a')
        or $cameralife->Error("Can't open ".$cameralife->base_dir.'/deleted.log', __FILE__, __LINE__);
      fwrite($fh, date('Y-m-d H:i:s')."\t".$this->record['path'].$this->record['filename']."\n");
      fclose($fh);
    }
  }

  public function Destroy()
  {
    if ($this->image)
      $this->image->Destroy();
  }

  public function GetMedia($size='thumbnail')
  {
    global $cameralife;
    if ($url = $cameralife->PhotoStore->GetURL($this, $size))
      return $url;

    if ($cameralife->GetPref('rewrite') == 'yes')
      return $cameralife->base_url."/photos/".$this->record['id'].'.'.$this->extension.'?'.($size=='photo'?'':'scale='.$size.'&amp;').'ver='.($this->record['mtime']+0);
    else
      return $cameralife->base_url.'/media.php&#63;id='.$this->record['id']."&amp;size=$size&amp;ver=".($this->record['mtime']+0);
  }

  public function GetFolder()
  {
    return new Folder($this->record['path'], FALSE);
  }

  // small or large
  /**
  *Enables you to set icon size as large or small
  */
  public function GetIcon($size='large')
  {
    global $cameralife;

    $retval = array('name'=>$this->record['description'],
                 'image'=>($size=='large')?$this->GetMedia():'small-photo',
                 'context'=>$this->record['hits'],
                 'width'=>$this->record['tn_width'],
                 'height'=>$this->record['tn_height']);

    if ($cameralife->GetPref('rewrite') == 'yes')
      $retval['href'] = $cameralife->base_url.'/photos/'.$this->record['id'];
    else
      $retval['href'] = $cameralife->base_url.'/photo.php&#63;id='.$this->record['id'];

    return $retval;
  }

  public function GetEXIF()
  {
    global $cameralife;

    $this->EXIF = array();
    $query = $cameralife->Database->Select('exif', '*', "photoid=".$this->record['id']);

    while ($row = $query->FetchAssoc()) {
      if ($row['tag'] == 'empty') continue;
      $this->EXIF[$row['tag']] = $row['value'];
    }

    return $this->EXIF;
  }

  public function LoadEXIF($file)
  {
    global $cameralife;

    $exif = @exif_read_data($file, 'IFD0', true);
    $this->EXIF = array();
    if ($exif===false)
      return $retval;
    else {
      if ($exif['EXIF']['DateTimeOriginal']) {
        $this->EXIF["Date taken"]=$exif['EXIF']['DateTimeOriginal'];
        $exifPieces = explode(" ", $this->EXIF["Date taken"]);
        $this->record['created'] = date("Y-m-d",strtotime(str_replace(":","-",$exifPieces[0])." ".$exifPieces[1]));
      }
      if ($model = $exif['IFD0']['Model']) {
        $this->EXIF["Camera Model"]=$model;
      }
      if ($fnumber = $exif['COMPUTED']['ApertureFNumber']) {
        $this->EXIF["Aperture"]=$fnumber;
      }
      if ($exposuretime = $exif['EXIF']['ExposureTime']) {
        $this->EXIF["Speed"]=$exposuretime;
      }
      if ($iso = $exif['EXIF']['ISOSpeedRatings']) {
        $this->EXIF["ISO"]=$iso;
      }
      if ($focallength = $exif['EXIF']['FocalLength']) {
        if(preg_match('#([0-9]+)/([0-9]+)#', $focallength, $regs))
        $focallength = $regs[1] / $regs[2];
        $this->EXIF["Focal distance"]="${focallength}mm";
      }
      if ($focallength) {
        $ccd = 35;
        if ($exif['COMPUTED']['CCDWidth'])
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
      if ($exif['GPS']) {
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

    $cameralife->Database->Delete('exif', 'photoid='.$this->record['id']);
    foreach ($this->EXIF as $tag=>$value)
      $cameralife->Database->Insert('exif', array('photoid'=>$this->record['id'], 'tag'=>$tag, 'value'=>$value));
  }

  // Returns an array of Icons of Views related to this photo
  // one of them will have $icon['class'] = 'referer'
  // We use the page's referer to find which is the referer
  // also sets $this->context

  /**The function Get Related returns an array of icons of possible views related to a photo.
  *One of the icon will be denoted as $icon['class']='referer'
  *The page's referer will be used to find the referer
  *The function also sets $this->context
  *
  *<code>if (eregi ("album.php\?id=([0-9]*)",$_SERVER['HTTP_REFERER'],$regs) || eregi("albums/([0-9]+)",$_SERVER['HTTP_REFERER'],$regs))</code>
  *The above line of code finds if the referer is an album
  *<code>$result = $cameralife->Database->Select('albums','id,name',"'".addslashes($this->Get('description'))."' LIKE CONCAT('%',term,'%')");</code>
  *Find all albums that contain this photo(this is incomplete and will be updated in the upcoming version)
  *<code> if (eregi ("q=([^&]*)",$_SERVER['HTTP_REFERER'],$regs))</code>
  *Checks if retrieved from a search
  *<code>$search = new Search($this->Get('description'));</code>
  *Search for photos named as a user given description
*/

  public function GetRelated()
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

      $icon = $album->GetTopic()->GetIcon('small');
      $icon['name'] = 'Other ' . $icon['name'];
      $retval[] = $icon;

      $icon = $album->GetIcon('small');
      $icon['class'] = 'referer tag';
      $retval[] = $icon;

      $this->context = $album;
    }

    // Find all albums that contain this photo, this is not 100%
    $result = $cameralife->Database->Select('albums','id,name',"'".addslashes($this->Get('description'))."' LIKE CONCAT('%',term,'%')");
    while ($albumrecord = $result->FetchAssoc()) {
//      if (is_a($this->context, 'Album') && $this->context->Get('id') == $album['id']) // PHP4
      if (($this->context instanceof Album) && $this->context->Get('id') == $albumrecord['id']) // PHP5
        continue;

      $album = new Album($albumrecord['id']);
      $retval[] = $album->GetIcon('small');
    }

    // Did they come from a search??

    if (isset($_SERVER['HTTP_REFERER']) &&
        preg_match("#q=([^&]*)#",$_SERVER['HTTP_REFERER'],$regs))
    {
      $search = new Search($regs[1]);
      $icon = $search->GetIcon('small');
      $icon['class'] = 'referer';
      $icon['href'] .= $extrasearch;
      $retval[] = $icon;

      $this->context = $search;
    } else {
      // Find all photos named exactly like this

      $search = new Search($this->Get('description'));
      $counts = $search->GetCounts();

      if ($counts['photos'] > 1) {
        $icon = $search->GetIcon('small');
        $icon['name'] = 'Photos named the same';
        $retval[] = $icon;
      }
    }

    if (strlen($this->Get('path')) > 0) {
      $icon = $this->GetFolder()->GetIcon('small');

      if (!$this->context) {
        $this->context = $this->GetFolder();
        $icon['class'] = 'referer';
        $icon['rel'] = 'directory'; // an anchor attribute, to add semantics
      }

      $retval[] = $icon;
    }

    return $retval;
  }

  // PRIVATE
  /**
  *@access private
  */

  /// Convert "2/4" to 0.5 and "4" to 4
  public function GPS2num($num)
  {
    $parts = explode('/', $num);
    if(count($parts) == 0)

      return 0;
    if(count($parts) == 1)

      return $parts[0];
    return floatval($parts[0]) / floatval($parts[1]);
  }

  public function GetContext()
  {
    if (!$this->context)
      $this->GetRelated();

    if (!count($this->contextPhotos)) {
      $this->context->SetPage(0,99);

      $this->contextPhotos = $this->context->GetPhotos(); /* Using the base class, how sexy is that? Uses base class ;a useful and convinient feature*/
      $last = new Photo();
      foreach ($this->contextPhotos as $cur) {
        if ($cur->Get('id') == $this->Get('id') && $last->Get('id'))
          $this->contextPrev = $last;
        if ($last->Get('id') == $this->Get('id'))
          $this->contextNext = $cur;
        $last = $cur;
      }

    }

    return $this->contextPhotos;
  }

  // returns the previous photo or false if none exists
  /**Returns previous photo
  *else false if none exists
  */

  public function GetPrevious()
  {
    if (!count($this->contextPhotos))
      $this->GetContext();

    return $this->contextPrev;
  }

  // returns the next photo or false if none exists
  public function GetNext()
  {
    if (!count($this->contextPhotos))
      $this->GetContext();

    return $this->contextNext;
  }

}
