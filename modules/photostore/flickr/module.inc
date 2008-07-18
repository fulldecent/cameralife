<?php
  /*
  *  This module stores files on a remote filesystem.
  *  That remote server must support public HTTP.
  *  Private files are delivered through this server.
  *  Lazy thumbnailing is not supported.
  *
  */

  require dirname(__FILE__) . '/phpFlickr-2.2.0/phpFlickr.php';

  class FlickrPhotoStore extends PhotoStore
  {
    function FlickrPhotoStore() 
    {
      global $_GET, $_SESSION, $cameralife;

      $this->name = 'Flickr';

      $this->about = 'Before you set the api key and sercet here, <a href="http://www.flickr.com/services/api/keys/">go to flickr</a> and set your callback URL to the URL of this page! When you have done this, <a href="?flickrlogin=true">click here</a>.';

      $this->preferences[] = array('name'=>'service', 'desc'=>'Service', 'type'=>array('flickr'=>'flickr.com','23'=>'23hq.com','zooomr'=>'zooomr.com'), 'default'=>'flickr');
      $this->preferences[] = array('name'=>'api_key', 'desc'=>'Flickr API Key', 'type'=>'string', 'default'=>'SET THIS');
      $this->preferences[] = array('name'=>'api_secret', 'desc'=>'Flickr API Secret', 'type'=>'string', 'default'=>'SET THIS');
      $this->preferences[] = array('name'=>'flickr_auth_token', 'desc'=>'Auth Token', 'type'=>'string', 'default'=>'DO NOT SET THIS');
      $this->preferences[] = array('name'=>'photoset_id', 'desc'=>'Photoset ID', 'type'=>'string', 'default'=>'SET THIS');

      if ($this->GetPref('api_key') != 'SET THIS' && $this->GetPref('api_secret') != 'SET THIS')
      {
        $this->Flickr = new phpFlickr($this->GetPref('api_key'), $this->GetPref('api_secret'));
        $this->Flickr->service = $this->GetPref('service');

        if ($_GET['frob'])
        {
           $this->Flickr->auth_getToken($_GET['frob'], $_SESSION['phpFlickr_auth_token']);
          $this->SetPref('flickr_auth_token', $_SESSION['phpFlickr_auth_token']);
        }

        if ($_GET['flickrlogin'])
        {
          $this->Flickr->auth('delete', FALSE);
        }

        $this->Flickr->setToken($this->GetPref('flickr_auth_token'));
      }

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

      list($photoid, $farmid, $serverid, $secret) = preg_split('/[-\.]/', $photo->Get('filename'));

      if ($type == 'thumbnail')
         $file = "http://farm$farmid.static.flickr.com/$serverid/${photoid}_${secret}_t.jpg";
      elseif ($type == 'scaled')
         $file = "http://farm$farmid.static.flickr.com/$serverid/${photoid}_${secret}.jpg";
      elseif ($type == 'photo')
         $file = "http://farm$farmid.static.flickr.com/$serverid/${photoid}_${secret}.jpg";

      $target = tempnam('', 'cameralife_');
      copy($file, $target);

      return array($target, TRUE, 0);
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
      
      $context = $photo->GetRelated();
      $tags = array();
      if (is_array($context))
      {
        foreach ($context as $rel)
        {
          $tags[] = preg_replace('/ /', '_', $rel['name']);
        }
      }
      $tags = join(' ', $tags);

      $this->Flickr->async_upload($filename, $photo->Get('description'), '', $tags, $photo->Get('status') == 0);
    }

    /**
    *  PutThumbnails
    *
    *  Flickr does thumbnails for us
    *
    *  @return none
    */
    function PutThumbnails($photo, $scaled, $thumbnail) 
    {
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

      list($photoid, $farmid, $serverid, $secret) = preg_split('/[-\.]/', $photo->Get('filename'));
      
      $this->Flickr->replace($filename, $photoid);
      if ($this->Flickr->getErrorCode() == 1)
        $cameralife->Error('You must have a PRO account ith Flickr to replace/modify existing photos');
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

echo 'FLICKR DELETING...';
#      $cameralife->Error('Erasing is not implemented for Flickr... yet', __FILE__, __LINE__);
    }

    /**
    *  ListFiles
    *
    *  Returns a list of all files in the photostore
    *
    *  @return an array of files, in the form 'path'=>basename(path)
    *  or FALSE on error
    */
    function ListFiles($path = '', $recur=TRUE)
    {
      global $cameralife;

      $result = $this->Flickr->photos_search(array('user_id'=>'me'));

      if ($result)
      {
        $retval = array();
        foreach ($result['photo'] as $photo)
          $retval['flickr/'.$photo['id'].'-'.$photo['farm'].'-'.$photo['server'].'-'.$photo['secret'].'.jpg'] = $photo['id'].'-'.$photo['farm'].'-'.$photo['server'].'-'.$photo['secret'].'.jpg';
        return $retval;
      }
      else
      {
        return FALSE;
      }
    }

  }
?>
