<?php
  # the class for getting and using photos
  
class Photo extends View
{
  var $record, $image;
  var $context; // an Album, Search or Folder of where the user came from to get to this photo
  var $contextPhotos; // photos from context
  var $contextPrev; // the previous photo in context
  var $contextNext; // the next photo in contex
  var $extension;

  # Pass nothing to get an empty Photo, or
  # Pass a photo ID to load a photo, or
  # Pass an array to create a photo:
  #
  #   Required fields: filename, path, username
  #   Optional fields: status, description, fsize, created
  #
  #   If making a new file, the caller is responsible for putting the
  #   new file in the photostore if it is not already there. Do that
  #   after instantiating this class
  function Photo($original = NULL)
  {
    global $cameralife;

    if (is_null($original))
    {
      $this->record['id'] = NULL;
    }
    elseif (is_numeric($original)) # This is an ID
    {
      $result = $cameralife->Database->Select('photos','*', "id=$original");
      $this->record = $result->FetchAssoc()
      ;
#        or $cameralife->Error("Cannot load photo: $original", __FILE__, __LINE__);
    }
    elseif(is_array($original)) # A new image, given by an array
    {
      $this->record['description'] = 'unnamed';
      $this->record['status'] = '0';
      $this->record['fsize'] = filesize($fullpath);
      $this->record['created'] = date('Y-m-d', filemtime($fullpath));
      $this->record = array_merge($this->record, $original);

      $this->record['id'] = $cameralife->Database->Insert('photos', $this->record);
      // Generate the thumbnail later, when requested
      //$this->GenerateThumbnail(); // Sets mtime, sizes, fsize
    }
    $this->context = false;
    $this->contextPrev = false;
    $this->contextNext = false;
    $this->contextPhotos = array();

    $path_parts = pathinfo($this->record['filename']);
    $this->extension = strtolower($path_parts['extension']);
  }

  function Set($key, $value)
  {
    global $cameralife;

    $receipt = NULL;
    if ($key != 'hits')
      $receipt = AuditTrail::Log('photo',$this->record['id'],$key,$this->record[$key],$value);
    $this->record[$key] = $value;
    $cameralife->Database->Update('photos', array($key=>$value), 'id='.$this->record['id']);
    return $receipt;
  }

  function Get($key)
  {
    return $this->record[$key];
  }

  // If you can guarantee record['modified'] is false, set $original to TRUE
  function LoadImage($original = FALSE)
  {
    global $cameralife;

    if (isset($this->image)) return;
    list ($file, $temp, $this->record['mtime']) = $cameralife->PhotoStore->GetFile($this);
    if ($original)
      $this->record['fsize'] = filesize($file);

    $this->image = $cameralife->ImageProcessing->CreateImage($file);
    if (!$this->image->Check()) $cameralife->Error("Bad photo processing: $origphotopath",__FILE__,__LINE__);
   
    if ($temp) unlink($file);
  }

  // If you can guarantee record['modified'] is false, set $original to TRUE
  // this is the cheapest/laziest way to get the metadata into the system
  function GenerateThumbnail($original = FALSE)
  {
    global $cameralife;

    $this->LoadImage($original);
    $imagesize = $this->image->GetSize();

    $scaled = tempnam('', 'cameralife_S');
    $this->image->Resize($scaled, 600);
    $thumbnail = tempnam('', 'cameralife_T');
    $thumbsize = $this->image->Resize($thumbnail, 150);
    $cameralife->PhotoStore->PutThumbnails($this, $scaled, $thumbnail);
    @unlink($scaled);
    @unlink($thumbnail);

    $this->record['width'] = $imagesize[0];
    $this->record['height'] = $imagesize[1];
    $this->record['tn_width'] = $thumbsize[0];
    $this->record['tn_height'] = $thumbsize[1];

    $cameralife->Database->Update('photos',$this->record,'id='.$this->record['id']);
  }

  function Rotate($angle)
  {
    global $cameralife;

    $this->LoadImage();
    $this->image->Rotate($angle);

    $temp = tempnam('', 'cameralife_');
    $this->image->Save($temp);
    $cameralife->PhotoStore->ModifyFile($this, $temp);
    unlink($temp);
    
    $this->record['modified'] = 1;
    $cameralife->Database->Update('photos',$this->record,'id='.$this->record['id']);
  }

  function Revert()
  {
    global $cameralife;

    if ($this->record['modified'])
    {
      $this->record['modified'] = 0;
      $cameralife->PhotoStore->ModifyFile($this, NULL);
    }

    $this->GenerateThumbnail(TRUE); # will commit $this->record
  }
 
  function Erase()
  {
    global $cameralife;

    $cameralife->PhotoStore->EraseFile($this);
    $cameralife->Database->Delete('photos','id='.$this->record['id']);
    $cameralife->Database->Delete('logs',"record_type='photo' AND record_id=".$this->record['id']);
    $cameralife->Database->Delete('ratings',"id=".$this->record['id']);
    $cameralife->Database->Delete('comments',"photo_id=".$this->record['id']);
    $this->Destroy();

    # Bonus code
    $fh = fopen($cameralife->base_dir.'/deleted.log', 'a')
      or $cameralife->Error("Can't open ".$cameralife->base_dir.'/deleted.log', __FILE__, __LINE__);
    fwrite($fh, date('Y-m-d H:i:s')."\t".$this->record['path'].$this->record['filename']."\n");
    fclose($fh);
  }

  function Destroy()
  {
    if ($this->image)
      $this->image->Destroy();
  }

  function GetMedia($type='thumbnail')
  {
    global $cameralife;

    return $cameralife->PhotoStore->GetURL($this, $type);
  }

  function GetFolder()
  {
    return new Folder($this->record['path'], FALSE);
  }

  function GetIcon($size='large')
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
      $retval['href'] = $cameralife->base_url.'/photo.php?id='.$this->record['id'];
 
    return $retval;
  }

  function GetEXIF()
  {
    global $cameralife;

return(array('Date'=>'now', 'camera'=>'a cool camera', 'fix me'=>'YES PLESA!!'));

    $exif = @exif_read_data($cameralife->preferences['core']['photo_dir'].'/'.$this->record['path'].$this->record['filename'], 'IFD0', true);
    $retval = array();

    if ($exif===false) return $retval;

    else
    {
      if ($exif['EXIF']['DateTimeOriginal'])
      {
        $retval["Date taken"]=$exif['EXIF']['DateTimeOriginal'];
      }
      if ($model = $exif['IFD0']['Model'])
      {
        $retval["Camera Model"]=$model;
      }
      if ($fnumber = $exif['COMPUTED']['ApertureFNumber'])
      {
        $retval["Aperture"]=$fnumber;
      }
      if ($exposuretime = $exif['EXIF']['ExposureTime'])
      {
        $retval["Speed"]=$exposuretime;
      }
      if ($iso = $exif['EXIF']['ISOSpeedRatings'])
      {
        $retval["ISO"]=$iso;
      }
      if ($focallength = $exif['EXIF']['FocalLength'])
      {
        if(ereg('([0-9]+)/([0-9]+)', $focallength, $regs))
        $focallength = $regs[1] / $regs[2];
        $retval["Focal distance"]="${focallength}mm";
      }
      if ($focallength)
      {
        $ccd = 35;
        if ($exif['COMPUTED']['CCDWidth'])
        $ccd = str_replace('mm','',$exif['COMPUTED']['CCDWidth']);
        $fov = round(2*rad2deg(atan($ccd/2/$focallength)),2);
        // http://www.rags-int-inc.com/PhotoTechStuff/Lens101/
        
        $retval["Field of view"]="${fov}&deg; horizontal";
      }
      if ($focallength && $exposuretime)
      {
        if (!$iso) $iso = 100;
        if ($exif['EXIF']['Flash'] % 2 == 1)
        $light = 'Flash';
        else
        {
          if (ereg('([0-9]+)/([0-9]+)', $exposuretime, $regs));
            $exposuretime = $regs[1] / $regs[2];
    
          $ev = pow(str_replace('f/','',$fnumber),2) / $iso / $exposuretime;
          if ($ev > 10)
            $light = 'Probably outdoors';
          else
            $light = 'Probably indoors';
        }
        $retval["Lighting"]=$light;
      }
    }

    return $retval;
  }

  // Returns an array of Icons of Views related to this photo
  // one of them will have $icon['class'] = 'referer'
  // We use the page's referer to find which is the referer
  // also sets $this->context
  function GetRelated()
  {
    global $_SERVER, $cameralife;

    $retval = array();
    $this->context = false;

    if (eregi ("start=([0-9]*)",$_SERVER['HTTP_REFERER'],$regs))
      $extrasearch = "&amp;start=".$regs[1];

    // Find if the referer is an album 
    if (eregi ("album.php\?id=([0-9]*)",$_SERVER['HTTP_REFERER'],$regs) || eregi("albums/([0-9]+)",$_SERVER['HTTP_REFERER'],$regs))
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
    while ($albumrecord = $result->FetchAssoc())
    {
//      if (is_a($this->context, 'Album') && $this->context->Get('id') == $album['id']) // PHP4
      if (($this->context instanceof Album) && $this->context->Get('id') == $albumrecord['id']) // PHP5
        continue;

      $album = new Album($albumrecord['id']);
      $retval[] = $album->GetIcon('small');
    }

    // Did they come from a search??
    if (eregi ("q=([^&]*)",$_SERVER['HTTP_REFERER'],$regs))
    {
      $search = new Search($regs[1]);
      $icon = $search->GetIcon('small');
      $icon['class'] = 'referer';
      $icon['href'] .= $extrasearch;
      $retval[] = $icon;

      $this->context = $search;
    }
    else
    {
      // Find all photos named exactly like this
      $search = new Search($this->Get('description'));
      $counts = $search->GetCounts();

      if ($counts['photos'] > 1)
      {
        $icon = $search->GetIcon('small');
        $icon['name'] = 'Photos named the same';
        $retval[] = $icon;
      }
    }

    if (strlen($this->Get('path')) > 0)
    {
      $icon = $this->GetFolder()->GetIcon('small');

      if (!$this->context)
      {
        $this->context = $this->GetFolder();
        $icon['class'] = 'referer';
        $icon['rel'] = 'directory'; // an anchor attribute, to add semantics
      }

      $retval[] = $icon;
    }

    return $retval;
  }

  // private?
  function GetContext()
  {
    if (!$this->context)
      $this->GetRelated();

    if (!count($this->contextPhotos))
    {
      $this->context->SetPage(0,99);

      $this->contextPhotos = $this->context->GetPhotos(); /* Using the base class, how sexy is that? */
      $last = new Photo();
      foreach ($this->contextPhotos as $cur)
      {
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
  function GetPrevious()
  {
    if (!count($this->contextPhotos))
      $this->GetContext();

    return $this->contextPrev;
  }

  // returns the next photo or false if none exists
  function GetNext()
  {
    if (!count($this->contextPhotos))
      $this->GetContext();

    return $this->contextNext;
  }

}
?>
