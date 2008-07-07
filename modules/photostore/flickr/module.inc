<?php
  /*
  *  This module stores files on a remote filesystem.
  *  That remote server must support public HTTP.
  *  Private files are delivered through this server.
  *  Lazy thumbnailing is not supported.
  *
  */

  class FlickrPhotoStore extends PhotoStore
  {
    function FlickrPhotoStore() 
    {
      $this->name = 'Flickr';

      $this->preferences[] = array('name'=>'api_key', 'desc'=>'Flickr API Key', 'type'=>'string', 'default'=>'SET THIS');
      $this->preferences[] = array('name'=>'photoset_id', 'desc'=>'Photoset ID', 'type'=>'string', 'default'=>'SET THIS');
    } 

    /**
    *  GetURL
    *
    *  Gets a URL for the client to access the selected resource.
    *  (Implementation note: make sure non-public photos do not have 
    *  publicly accessible urls)
    *
    *  @param $photo - a target photo obeject
    *  @param $type - one of ('photo', 'scaled', 'thumbnail')
    *
    *  @return a URL
    */
    function GetURL($photo, $type='photo') 
    {
      global $cameralife;

      list($photoid, $farmid, $serverid, $secret) = preg_split('/[-\.]/', $photo->Get('filename'));
#var_dump($photoid, $farmid, $secret);

     if ($type == 'thumbnail')
        return "http://farm$farmid.static.flickr.com/$serverid/${photoid}_${secret}_t.jpg";
     elseif ($type == 'scaled')
        return "http://farm$farmid.static.flickr.com/$serverid/${photoid}_${secret}.jpg";
     elseif ($type == 'photo')
        return "http://farm$farmid.static.flickr.com/$serverid/${photoid}_${secret}.jpg";
    }

    /**
    *  GetFile
    *
    *  Gets a filename for the requested resource. It is downloaded if necessary.
    *
    *  @param $photo - a target photo object
    *  @param $type - one of ('photo', 'original', 'modified', 'scaled', 'thumbnail')
    *    'photo' refers to the modified (if exists) otherwise original
    *
    *  @return an array with two elements:
    *     filename - string - the local file you requested
    *     temporary - boolean
    *     mtime ...
    *
    *  If temporary is set, the caller is responsible for deleting this file when done.
    */
    function GetFile($photo, $type='photo') 
    {
      global $cameralife;

      $path_parts = pathinfo($photo->Get('filename'));
      $extension = strtolower($path_parts['extension']);

      if ($type == 'thumbnail')
         $file = "http://farm$farmid.static.flickr.com/$serverid/${photoid}_${secret}_t.jpg";
      elseif ($type == 'scaled')
         $file = "http://farm$farmid.static.flickr.com/$serverid/${photoid}_${secret}.jpg";
      elseif ($type == 'photo')
         $file = "http://farm$farmid.static.flickr.com/$serverid/${photoid}_${secret}.jpg";

      $target = tempnam('', 'cameralife_');
     
      copy($file, $target);
      return array('filename'=>$target, 'temporary'=>TRUE, 'mtime'=>0);
    }

    /**
    *  PutFile
    *
    *  Puts a new photo into the photostore. 
    *
    *  @param $photo - a target photo object
    *  @param $filename - the local filesystem file that we are putting
    *
    *  @return none
    */
    function PutFile($photo, $filename) 
    {
      global $cameralife;

      $cameralife->Error('Uploading is not implemented for Flickr... yet', __FILE__, __LINE__);
    }

    /**
    *  PutThumbnails
    *
    *  Puts a a scaled photo and thumbnail into the photostore. 
    *
    *  @param $photo - a target photo object
    *  @param $scaled - the file to insert
    *  @param $thumbnail - the file to insert
    *
    *  @return none
    */
    function PutThumbnails($photo, $scaled, $thumbnail) 
    {
      global $cameralife;

      return;
    }

    /**
    *  CheckThumbnails
    *
    *  Ensures that thumbnails exist
    *
    *  @param $photo - a target photo object
    *
    *  @return true if work was done
    */
    function CheckThumbnails($photo)
    {
      return FALSE; # we always cache thumbnails, no doubt
    }

    /**
    *  ModifyFile
    *
    *  Puts a modified version of a photo into the photostore. Passing
    *  NULL for the filename will revert the photo.
    *
    *  @param $photo - a target photo object
    *  @param $filename - the local filesystem file that we are putting
    *
    *  @return none
    */
    function ModifyFile($photo, $filename) 
    {
      global $cameralife;

      $cameralife->Error('Uploading is not implemented for Flickr... yet', __FILE__, __LINE__);
    }


    /**
    *  EraseFile
    *
    *  Erase a photo and associated scaled photos from the system
    *
    *  @param $photo - a target photo object
    *
    *  @return none
    */
    function EraseFile($photo) 
    {
      global $cameralife;

      $cameralife->Error('Erasing is not implemented for Flickr... yet', __FILE__, __LINE__);
    }

    /**
    *  ListFiles
    *
    *  Returns a list of all files in the photostore
    *
    *  @return an array of files, in the form 'path'=>basename(path)
    */
    function ListFiles($path = '', $recur=TRUE)
    {
      global $cameralife;

      $params = array(
        'api_key'	=> $this->GetPref('api_key'),
        'method'	=> 'flickr.photosets.getPhotos',
        'photoset_id'	=> $this->GetPref('photoset_id'),
        'format'	=> 'php_serial',
      );

      $encoded_params = array();
      foreach ($params as $k => $v){
        $encoded_params[] = urlencode($k).'='.urlencode($v);
      }

      $url = "http://api.flickr.com/services/rest/?".implode('&', $encoded_params);
      $rsp = file_get_contents($url);
      $rsp_obj = unserialize($rsp);

#die(var_dump($rsp_obj));
      if ($rsp_obj['stat'] == 'ok')
      {
        $retval = array();
        foreach ($rsp_obj['photoset']['photo'] as $photo)
          $retval['flickr/'.$photo['id'].'-'.$photo['farm'].'-'.$photo['server'].'-'.$photo['secret'].'.jpg'] = $photo['id'].'-'.$photo['farm'].'-'.$photo['server'].'-'.$photo['secret'].'.jpg';
        return $retval;
      }
      else
      {
        $cameralife->Error('Flickr replied with an error: '.$rsp,__FILE__,__LINE__);
      }
    }

  }
?>
