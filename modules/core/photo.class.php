<?php
  /**Class Photo enables you to  get the photos
  *@link  http://fdcl.sourceforge.net/
    *@version 2.6.2
    *@author Will Entriken <cameralife@phor.net>
    *@access public
    *@copyright Copyright (c) 2001-2009 Will Entriken
  */
   /**
   *This class is for getting and using photos
   *@var mixed $context an Album, Search or Folder from where the user retrieved the photo
     *@var mixed $contextPhotos the photos from the 'context', that is an Album ,a Folder or a Search result
     *@var mixed $contextPrev the previous photo in the 'context'
  *@var mixed $contextNext the next photo in the 'context'
   */

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
  /**
  *To get an empty Photo pass nothing ie NULL
  *To load a photo pass a photo ID
  *<code> elseif (is_numeric($original)) </code>
  *To create a photo pass an array
  *<code>elseif(is_array($original))</code>
 *<b>Required fields Filename,path of file ,and username</b>
 *Optional fields Status ,description and file size
 */
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
      $this->record = $result->FetchAssoc();
#        or $cameralife->Error("Cannot load photo: $original", __FILE__, __LINE__);
    }
    elseif(is_array($original)) # A new image, given by an array
    {
      $this->record['description'] = 'unnamed';
      $this->record['status'] = '0';
      $this->record['fsize'] = filesize($fullpath);
      $this->record['created'] = date('Y-m-d');
      $this->recode['modified'] = '0';
      $this->recode['mtime'] = '0';
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
    if ($key == 'status')
      $cameralife->PhotoStore->SetPermissions($this);
    $this->record[$key] = $value;
    $cameralife->Database->Update('photos', array($key=>$value), 'id='.$this->record['id']);
    return $receipt;
  }
/**@ todo Do you think we need to destroy the original?We look forward to your feedback
*If you can guarantee record['modified'] is false, set $original to TRUE
 *If ORIGINAL is set, we can collect and use some extra metadata
*/
  function Get($key)
  {
    return $this->record[$key];
  }

//TODO kill original?
  // If you can guarantee record['modified'] is false, set $original to TRUE
  // If ORIGINAL is set, we can collect and use some extra metadata
  function LoadImage($original = FALSE)
  {
    global $cameralife;

    if (isset($this->image)) return;
    list ($file, $temp, $this->record['mtime']) = $cameralife->PhotoStore->GetFile($this);
    if ($this->record['original'])
    {
      $this->record['fsize'] = filesize($file);
      $this->LoadEXIF($file);
    }

    $this->image = $cameralife->ImageProcessing->CreateImage($file);
    if (!$this->image->Check()) $cameralife->Error("Bad photo processing: $origphotopath",__FILE__,__LINE__);

    if ($temp) unlink($file);
  }

  // If you can guarantee record['modified'] is false, set $original to TRUE
  // this is the cheapest/laziest way to get the metadata into the system
  /**
  *If you can guarantee record['modified'] is false, set $original to TRUE
  * this is the cheapest/laziest way to get the metadata into the system
  */
  function GenerateThumbnail($original = FALSE)
  {
    global $cameralife;

    $this->LoadImage($original);
    $imagesize = $this->image->GetSize();

    $sizes = preg_split('/[, ]+/',$cameralife->GetPref('optionsizes'));
    $sizes[] = $cameralife->GetPref('thumbsize');
    $sizes[] = $cameralife->GetPref('scaledsize');
    sort ($sizes);
    $files = array();

    while ($cursize = array_pop($sizes))
    {
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

  function Rotate($angle)
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

  function Revert()
  {
    global $cameralife;

    if ($this->record['modified'])
    {
      $this->record['modified'] = 0;
      $cameralife->PhotoStore->ModifyFile($this, NULL);
      $this->record['mtime'] = 0;
    }

    $cameralife->Database->Update('photos',$this->record,'id='.$this->record['id']);
  }

  function Erase()
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
    if (file_exists($cameralife->base_dir.'/deleted.log'))
    {
      $fh = fopen($cameralife->base_dir.'/deleted.log', 'a')
        or $cameralife->Error("Can't open ".$cameralife->base_dir.'/deleted.log', __FILE__, __LINE__);
      fwrite($fh, date('Y-m-d H:i:s')."\t".$this->record['path'].$this->record['filename']."\n");
      fclose($fh);
    }
  }

  function Destroy()
  {
    if ($this->image)
      $this->image->Destroy();
  }

  function GetMedia($type='thumbnail')
  {
    global $cameralife;
    if ($url = $cameralife->PhotoStore->GetURL($this, $type))
      return $url;

    if ($cameralife->GetPref('rewrite') == 'yes')
      return $cameralife->base_url."/photos/$type/".$this->record['id'].'.'.$this->extension.'?'.($this->record['mtime']+0);
    else
      return $cameralife->base_url.'/media.php&#63;format='.$type.'&amp;id='.$this->record['id'].'&amp;ver='.($this->record['mtime']+0);
  }

  function GetFolder()
  {
    return new Folder($this->record['path'], FALSE);
  }

  // small or large
  /**
  *Enables you to set icon size as large or small
  */
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
      $retval['href'] = $cameralife->base_url.'/photo.php&#63;id='.$this->record['id'];

    return $retval;
  }

  function GetEXIF()
  {
    global $cameralife;

    $retval = array();
    $query = $cameralife->Database->Select('exif', '*', "photoid=".$this->record['id']);

    while($row = $query->FetchAssoc())
    {
      if ($row['tag'] == 'empty') continue;
      $retval[$row['tag']] = $row['value'];
    }

    return $retval;
  }

  function LoadEXIF($file)
  {
    global $cameralife;

    $exif = @exif_read_data($file, 'IFD0', true);
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
        //@link http://www.rags-int-inc.com/PhotoTechStuff/Lens101/

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

    if (!count($retval)) $retval=array('empty'=>'true');

    $cameralife->Database->Delete('exif', 'photoid='.$this->record['id']);
    foreach ($retval as $tag=>$value)
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

  // PRIVATE
  /**
  *@access private
  */

  function GetContext()
  {
    if (!$this->context)
      $this->GetRelated();

    if (!count($this->contextPhotos))
    {
      $this->context->SetPage(0,99);

      $this->contextPhotos = $this->context->GetPhotos(); /* Using the base class, how sexy is that? Uses base class ;a useful and convinient feature*/
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
  /**Returns previous photo
  *else false if none exists
  */

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
