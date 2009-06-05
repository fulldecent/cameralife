<?php
  /**This module stores files on a remote filesystem
  *  .That remote server must support public HTTP.
  *  Private files are delivered through this server.
  *
  *Does not support
  *<ul>
  *<li>Lazy thumbnailing</li>
  *
  *<li>The deleted dir</li></ul>
  *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  *@link http://fdcl.sourceforge.net
    */
  /**Stores files remotely on the Amazon S3 cloud
  *
  *You can use an Amazon S3 account or 
  *
  *<a href="https://aws-portal.amazon.com/gp/aws/user/subscription/index.html?offeringCode=2CE0E367">sign up for Camera Life Photo Storage</a>.';
  *   
  *<code>$this->SetPref('created', '1');</code>'Initializes AWS buckets...';
  *<code>$fp = fsockopen("s3.amazonaws.com", 80, $errno, $errstr, 30);
  * if (!$fp)
   * $cameralife->Error("Cannot connect to AWS: $errstr ($errno)\n");</code>Opens HTTP connection to Amazon S3
    *<code>$aws_bucket = $this->GetPref('photobucket'); // AWS bucket 
       * $dt = gmdate('r'); // GMT based timestamp 
      *  $string2sign = "PUT\n\n\n{$dt}\nx-amz-acl:public-read\n/{$aws_bucket}";</code>Creates the first bucket
      *<code>  $query = "PUT /{$aws_bucket} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nx-amz-acl: public-read\r\nConnection: keep-alive\r\nDate: $dt\r\nAuthorization: AWS ".$this->GetPref('accesskey').":".$this->amazon_hmac($string2sign)."\r\n\r\n";
*/<code> Prepares HTTP PUT query
*<code>$aws_bucket = $this->GetPref('cachebucket'); // AWS bucket 
     *   $dt = gmdate('r'); // GMT based timestamp 
     *   $string2sign = "PUT\n\n\n{$dt}\nx-amz-acl:public-read\n/{$aws_bucket}";
*/<code>Creates the second bucket
  */

  class AmazonS3PhotoStore extends PhotoStore
  {
    function AmazonS3PhotoStore() 
    {
      global $_GET, $_SESSION, $cameralife;

      $this->name = 'Amazon S3';

      $this->about = 'Stores files remotely on the Amazon S3 cloud. You can use an Amazon S3 account or <a href="https://aws-portal.amazon.com/gp/aws/user/subscription/index.html?offeringCode=2CE0E367">sign up for Camera Life Photo Storage</a>.';

      $this->preferences[] = array('name'=>'accesskey', 'desc'=>'Your access key', 'type'=>'string', 'default'=>'SET THIS');
      $this->preferences[] = array('name'=>'secretaccesskey', 'desc'=>'Your sercet access key', 'type'=>'string', 'default'=>'SET THIS');
      $this->preferences[] = array('name'=>'photobucket', 'desc'=>'Bucket for Photos (needs to be globally unique, i.e. no other AWS user thought of this before)', 'type'=>'string', 'default'=>'myphotos');
      $this->preferences[] = array('name'=>'cachebucket', 'desc'=>'Bucket for cache files (needs to be globally unique, i.e. no other AWS user thought of this before)', 'type'=>'string', 'default'=>'mycache');
      $this->preferences[] = array('name'=>'created', 'desc'=>'Have the buckets already been created?', 'type'=>'yesno', 'default'=>'no');

      if (($this->GetPref('accesskey') != 'SET THIS') && ($this->GetPref('secretaccesskey') != 'SET THIS') && (!$this->GetPref('created')))
      {
#        echo 'Initializing AWS buckets...';
        $this->SetPref('created', '1');

        if (strlen($this->GetPref('secretaccesskey')) != 40)
          $cameralife->Error("$aws_secret should be exactly 40 bytes long", __FILE__, __LINE__);

        // opening HTTP connection to Amazon S3
        $fp = fsockopen("s3.amazonaws.com", 80, $errno, $errstr, 30);
        if (!$fp)
          $cameralife->Error("Cannot connect to AWS: $errstr ($errno)\n");


        // Make first bucket...
        $aws_bucket = $this->GetPref('photobucket'); // AWS bucket 
        $dt = gmdate('r'); // GMT based timestamp 
        $string2sign = "PUT\n\n\n{$dt}\nx-amz-acl:public-read\n/{$aws_bucket}";

        // preparing HTTP PUT query
        $query = "PUT /{$aws_bucket} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nx-amz-acl: public-read\r\nConnection: keep-alive\r\nDate: $dt\r\nAuthorization: AWS ".$this->GetPref('accesskey').":".$this->amazon_hmac($string2sign)."\r\n\r\n";

        $resp = $this->sendREST($fp, $query);
        if (strpos($resp, '<Error>') !== false)
          $cameralife->Error("Bucket creation failed: $resp", __FILE__, __LINE__);

        // Make second bucket...
        $aws_bucket = $this->GetPref('cachebucket'); // AWS bucket 
        $dt = gmdate('r'); // GMT based timestamp 
        $string2sign = "PUT\n\n\n{$dt}\nx-amz-acl:public-read\n/{$aws_bucket}";

        // preparing HTTP PUT query
        $query = "PUT /{$aws_bucket} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nx-amz-acl: public-read\r\nConnection: keep-alive\r\nDate: $dt\r\nAuthorization: AWS ".$this->GetPref('accesskey').":".$this->amazon_hmac($string2sign)."\r\n\r\n";

        $resp = $this->sendREST($fp, $query);
        if (strpos($resp, '<Error>') !== false)
          $cameralife->Error("Bucket creation failed: $resp", __FILE__, __LINE__);

        fclose($fp);

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
    *  @return mixed URL|FALSE If FALSE is returned ,it is handled by media.php
    */
    function GetURL($photo, $type='photo') 
    {
      global $cameralife;

      if ($photo->Get('status') == 0) #public
      {
        $extesnion = $photo->extension;

        if ($type=='photo')
        {
          if ($photo->Get('modified'))
            return 'http://s3.amazonaws.com/'.$this->GetPref('cachebucket').'/'.$photo->Get('id').'_mod.'.$extension;
          else
            return 'http://s3.amazonaws.com/'.$this->GetPref('photobucket').'/'.$photo->Get('path').$photo->Get('filename');
        }
        elseif ($type=='scaled')
          return 'http://s3.amazonaws.com/'.$this->GetPref('cachebucket').'/'.$photo->Get('id').'_'.$cameralife->GetPref('scaledsize').'.'.$extension;
        else // ($type=='thumbnail')
          return 'http://s3.amazonaws.com/'.$this->GetPref('cachebucket').'/'.$photo->Get('id').'_'.$cameralife->GetPref('thumbsize').'.'.$extension;
      }

      return FALSE; # request handled through media.php
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
    *@todo
    *<code>if (strpos($header, '200 OK') === false)  checks for error
       * $cameralife->Error("Error GETting file from AWS: $header -- $resp", __FILE__, __LINE__); // response code is not 200 OK -- failure
*</code>
    */
    function GetFile($photo, $type='photo') 
    {
      global $cameralife;

      $extesnion = $photo->extension;

      if ($type == 'photo')
      {
        if ($photo->Get('modified'))
          $file = $this->GetPref('cachebucket') .'/'. $photo->Get('id').'_mod.'.$extension;
        else
          $file = $this->GetPref('photobucket') .'/'. $photo->Get('path').$photo->Get('filename');
      }
      elseif ($type == 'original')
        $file = $this->GetPref('photobucket') .'/'. $photo->Get('path').$photo->Get('filename');
      elseif ($type == 'modified')
        $file = $this->GetPref('cachebucket') .'/'. $photo->Get('id').'_mod.'.$extension;
      elseif ($type == 'scaled')
        $file = $this->GetPref('cachebucket').'/'.$photo->Get('id').'_'.$cameralife->GetPref('scaledsize').'.'.$extension;
      elseif ($type == 'thumbnail')
        $file = $this->GetPref('cachebucket').'/'.$photo->Get('id').'_'.$cameralife->GetPref('thumbsize').'.'.$extension;

      $aws_key = $this->GetPref('accesskey');
      $aws_secret = $this->GetPref('secretaccesskey');
      $aws_path = urlencode($file);        // AWS object name (file name)

      $dt = gmdate('r'); // GMT based timestamp

      // preparing String to Sign    (see AWS S3 Developer Guide)
      $string2sign = "GET\n\n\n{$dt}\n/{$aws_path}";

      // preparing HTTP PUT query
      $query = "GET /{$aws_path} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nDate: $dt\r\nConnection: close\r\nAuthorization: AWS {$aws_key}:".$this->amazon_hmac($string2sign)."\r\n\r\n";

      list($header, $resp) = $this->downloadREST($fp, $query);
//TODO this is inefficient if we just uploaded the file

      if (strpos($header, '200 OK') === false) // checking for error
        $cameralife->Error("Error GETting file from AWS: $header -- $resp", __FILE__, __LINE__); // response code is not 200 OK -- failure

      $target = tempnam('', 'cameralife_');
      @$fh = fopen($target, 'wb');

      if ($fh == false) 
        $cameralife->Error("Can't open file {$target} for writing. Fatal error!", __FILE__, __LINE__);
    
      fwrite($fh, $resp);
      fclose($fh);

      return array($target, TRUE, strlen($resp)); 
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

      $aws_key = $this->GetPref('accesskey');
      $source_file = $filename; // file to upload to S3
      $file_type = mime_content_type($source_file);  // or other file type like "image/jpeg" for JPEG image, 
      $aws_bucket = $this->GetPref('photobucket'); // AWS bucket 
      $aws_object = urlencode($photo->Get('path').$photo->Get('filename'));         // AWS object name (file name)

      $file_data = file_get_contents($source_file);
      if ($file_data == false) die("Failed to read file ".$source_file);

      $file_length = strlen($file_data); // for Content-Length HTTP field 

      // opening HTTP connection to Amazon S3
      $fp = fsockopen("s3.amazonaws.com", 80, $errno, $errstr, 30);
      if (!$fp)
        $cameralife->Error("Cannot connect to AWS: $errstr ($errno)\n");

      $dt = gmdate('r'); // GMT based timestamp

      $access = $photo->Get('status') == 0 ? 'public-read' : 'private';

      // preparing String to Sign    (see AWS S3 Developer Guide)
      $string2sign = "PUT\n\n{$file_type}\n{$dt}\nx-amz-acl:{$access}\n/{$aws_bucket}/{$aws_object}";

      // preparing HTTP PUT query
      $query = "PUT /{$aws_bucket}/{$aws_object} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nx-amz-acl: ${access}\r\nConnection: keep-alive\r\nContent-Type: {$file_type}\r\nContent-Length: {$file_length}\r\nDate: $dt\r\nAuthorization: AWS {$aws_key}:".$this->amazon_hmac($string2sign)."\r\n\r\n";
      $query .= $file_data;

      $resp = $this->sendREST($fp, $query);
      if (strpos($resp, '<Error>') !== false)
        $cameralife->Error("AWS put failed: $resp", __FILE__, __LINE__);

      fclose($fp);

      $photo->GenerateThumbnail();
    }

//TODO
    # hm??? any thoughts?
    /**@todo We look forward to your inputs.
    */
    function SetPermissions() {}

    /**
    *  PutThumbnails
    *
    *  @return none
    */
    function PutThumbnails($photo, $thumbs) 
    {
      global $cameralife;

      $extesnion = $photo->extension;

      $aws_key = $this->GetPref('accesskey');
      $aws_secret = $this->GetPref('secretaccesskey');

      foreach ($thumbs as $size=>$file)
      {
        $source_file = $file; // file to upload to S3
        $file_type = mime_content_type($source_file);  // or other file type like "image/jpeg" for JPEG image, 
        $aws_bucket = $this->GetPref('cachebucket'); // AWS bucket 
        $aws_object = urlencode($photo->Get('id').'_'.$size.'.'.$extension);     // AWS object name (file name)

        $file_data = file_get_contents($source_file);
        if ($file_data == false) 
          $cameralife->Error("Failed to read file ".$source_file, __FILE__, __LINE__);

        $file_length = strlen($file_data); // for Content-Length HTTP field 

        // opening HTTP connection to Amazon S3
        $fp = fsockopen("s3.amazonaws.com", 80, $errno, $errstr, 30);
        if (!$fp)
          $cameralife->Error("Cannot connect to AWS: $errstr ($errno)\n");

        $dt = gmdate('r'); // GMT based timestamp

        $access = $photo->Get('status') == 0 ? 'public-read' : 'private';

        // preparing String to Sign    (see AWS S3 Developer Guide)
        $string2sign = "PUT\n\n{$file_type}\n{$dt}\nx-amz-acl:{$access}\n/{$aws_bucket}/{$aws_object}";

        // preparing HTTP PUT query
        $query = "PUT /{$aws_bucket}/{$aws_object} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nx-amz-acl: ${access}\r\nConnection: keep-alive\r\nContent-Type: {$file_type}\r\nContent-Length: {$file_length}\r\nDate: $dt\r\nAuthorization: AWS {$aws_key}:".$this->amazon_hmac($string2sign)."\r\n\r\n";
        $query .= $file_data;

        $resp = $this->sendREST($fp, $query);
        if (strpos($resp, '<Error>') !== false)
          $cameralife->Error("AWS put failed: $resp", __FILE__, __LINE__);
      }
      fclose($fp);
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
      $extesnion = $photo->extension;

      if($filename)
      {
        $aws_key = $this->GetPref('accesskey');
        $source_file = $filename; // file to upload to S3
        $file_type = mime_content_type($source_file);  // or other file type like "image/jpeg" for JPEG image, 
        $aws_bucket = $this->GetPref('cachebucket'); // AWS bucket 
        $aws_object = urlencode($photo->Get('id').'_mod.'.$extension);         // AWS object name (file name)

        $file_data = file_get_contents($source_file);
        if ($file_data == false) die("Failed to read file ".$source_file);

        $file_length = strlen($file_data); // for Content-Length HTTP field 

        // opening HTTP connection to Amazon S3
        $fp = fsockopen("s3.amazonaws.com", 80, $errno, $errstr, 30);
        if (!$fp)
          $cameralife->Error("Cannot connect to AWS: $errstr ($errno)\n");

        $dt = gmdate('r'); // GMT based timestamp
        $access = $photo->Get('status') == 0 ? 'public-read' : 'private';

        // preparing String to Sign    (see AWS S3 Developer Guide)
        $string2sign = "PUT\n\n{$file_type}\n{$dt}\nx-amz-acl:{$access}\n/{$aws_bucket}/{$aws_object}";

        // preparing HTTP PUT query
        $query = "PUT /{$aws_bucket}/{$aws_object} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nx-amz-acl: ${access}\r\nConnection: keep-alive\r\nContent-Ty
pe: {$file_type}\r\nContent-Length: {$file_length}\r\nDate: $dt\r\nAuthorization: AWS {$aws_key}:".$this->amazon_hmac($string2sign)."\r\n\r\n";
        $query .= $file_data;

        $resp = $this->sendREST($fp, $query);
        if (strpos($resp, '<Error>') !== false)
          $cameralife->Error("AWS put failed: $resp", __FILE__, __LINE__);

        fclose($fp);
        unlink($filename);
      }
      else
      {
        $extesnion = $photo->extension;
        $aws_key = $this->GetPref('accesskey');
        $aws_bucket = $this->GetPref('photobucket'); // AWS bucket 
        $aws_object = urlencode($photo->Get('id').'_mod.'.$extension);         // AWS object name (file name)

        // opening HTTP connection to Amazon S3
        $fp = fsockopen("s3.amazonaws.com", 80, $errno, $errstr, 30);
        if (!$fp)
          $cameralife->Error("Cannot connect to AWS: $errstr ($errno)\n");

        $dt = gmdate('r'); // GMT based timestamp

        // preparing String to Sign    (see AWS S3 Developer Guide)
        $string2sign = "DELETE\n\n\n{$dt}\n/{$aws_bucket}/{$aws_object}";
        // preparing HTTP PUT query
        $query = "DELETE /{$aws_bucket}/{$aws_object} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nConnection: keep-alive\r\nDate: $dt\r\nAuthorization: AWS
 {$aws_key}:".$this->amazon_hmac($string2sign)."\r\n\r\n";

        $resp = $this->sendREST($fp, $query);
        if (strpos($resp, '<Error>') !== false)
          $cameralife->Error("AWS delete failed: $resp", __FILE__, __LINE__);

        fclose($fp);
      }
    }


    /**
    *  EraseFile
    *
    *  Erase a photo and associated scaled photos from the system
    *
    *  @param mixed $photo - a target photo object
    *   
    */
    function EraseFile($photo) 
    {
      global $cameralife;

      $extesnion = $photo->extension;
#######
      $aws_key = $this->GetPref('accesskey');
      $aws_bucket = $this->GetPref('photobucket'); // AWS bucket 
      $aws_object = urlencode($photo->Get('path').$photo->Get('filename'));         // AWS object name (file name)

      // opening HTTP connection to Amazon S3
      $fp = fsockopen("s3.amazonaws.com", 80, $errno, $errstr, 30);
      if (!$fp)
        $cameralife->Error("Cannot connect to AWS: $errstr ($errno)\n");

      $dt = gmdate('r'); // GMT based timestamp

      // preparing String to Sign    (see AWS S3 Developer Guide)
      $string2sign = "DELETE\n\n\n{$dt}\n/{$aws_bucket}/{$aws_object}";

      // preparing HTTP PUT query
      $query = "DELETE /{$aws_bucket}/{$aws_object} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nConnection: keep-alive\r\nDate: $dt\r\nAuthorization: AWS {$aws_key}:".$this->amazon_hmac($string2sign)."\r\n\r\n";

      $resp = $this->sendREST($fp, $query);
      if (strpos($resp, '<Error>') !== false)
        $cameralife->Error("AWS delete failed: $resp", __FILE__, __LINE__);
#######
      $aws_bucket = $this->GetPref('cachebucket'); // AWS bucket 
      $aws_object = urlencode($photo->Get('id').'_'.$cameralife->GetPref('scaledsize').'.'.$extension);         // AWS object name (file name)
      $dt = gmdate('r'); // GMT based timestamp

      // preparing String to Sign    (see AWS S3 Developer Guide)
      $string2sign = "DELETE\n\n\n{$dt}\n/{$aws_bucket}/{$aws_object}";

      // preparing HTTP PUT query
      $query = "DELETE /{$aws_bucket}/{$aws_object} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nConnection: keep-alive\r\nHote: $dt\r\nAuthorization: AWS {$aws_key}:".$this->amazon_hmac($string2sign)."\r\n\r\n";

      $resp = $this->sendREST($fp, $query);
      if (strpos($resp, '<Error>') !== false)
        $cameralife->Error("AWS delete failed: $resp", __FILE__, __LINE__);
#######
      $aws_object = urlencode($photo->Get('id').'_150.'.$extension);         // AWS object name (file name)
      $dt = gmdate('r'); // GMT based timestamp

      // preparing String to Sign    (see AWS S3 Developer Guide)
      $string2sign = "DELETE\n\n\n{$dt}\n/{$aws_bucket}/{$aws_object}";

      // preparing HTTP PUT query
      $query = "DELETE /{$aws_bucket}/{$aws_object} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nConnection: keep-alive\r\nDate: $dt\r\nAuthorization: AWS {$aws_key}:".$this->amazon_hmac($string2sign)."\r\n\r\n";

      $resp = $this->sendREST($fp, $query);
      if (strpos($resp, '<Error>') !== false)
        $cameralife->Error("AWS delete failed: $resp", __FILE__, __LINE__);

      fclose($fp);
    }

    /**
    *  ListFiles
    *
    *  Returns a list of all files in the photostore
    *
    *  @return mixed array of files, in the form 'path'=>basename(path)
    *  or FALSE on error
    */
    function ListFiles($path = '', $recur=TRUE)
    {
      global $cameralife;

      $aws_key = $this->GetPref('accesskey');
      $aws_bucket = $this->GetPref('photobucket'); // AWS bucket 

      // opening HTTP connection to Amazon S3
      $fp = fsockopen("s3.amazonaws.com", 80, $errno, $errstr, 30);
      if (!$fp)
        $cameralife->Error("Cannot connect to AWS: $errstr ($errno)\n");

      $dt = gmdate('r'); // GMT based timestamp

      // preparing String to Sign    (see AWS S3 Developer Guide)
      $string2sign = "GET\n\n\n{$dt}\n/{$aws_bucket}";

      if ($path)
        $querystring = '?max-keys=1000&prefix='.$path.'&delimiter=/';
      else
        $querystring = '?max-keys=1000';

      ###
      $retval = array();
      ###

      $query = "GET /{$aws_bucket}$querystring HTTP/1.1\r\nHost: s3.amazonaws.com\r\nConnection: keep-alive\r\nDate: $dt\r\nAuthorization: AWS {$aws_key}:".$this->amazon_hmac($string2sign)."\r\n\r\n";

      $resp = $this->sendREST($fp, $query);
      if (strpos($resp, '<Error>') !== false)
        $cameralife->Error("AWS list failed: $resp", __FILE__, __LINE__);

      $xmlresp = simplexml_load_string($resp);

      foreach ($xmlresp->Contents as $c) {
        $retval[(string)$c->Key] = basename((string)$c->Key);
        $lastMarker = (string)$c->Key;
      }

      if (isset($xmlresp->IsTruncated) && (string)$xmlresp->IsTruncated == 'false') 
        return $retval;

      // Loop through truncated results if maxKeys isn't specified
      if ($lastMarker !== null && (string)$xmlresp->IsTruncated == 'true')
      do {
        $query = "GET /{$aws_bucket}$querystring&marker=${lastmarker} HTTP/1.1\r\nHost: s3.amazonaws.com\r\nConnection: keep-alive\r\nDate: $dt\r\nAuthorization: AWS {$aws_key}:".$this->amazon_hmac($string2sign)."\r\n\r\n";

        $resp = $this->sendREST($fp, $query);
        if (strpos($resp, '<Error>') !== false)
          $cameralife->Error("AWS list failed: $resp", __FILE__, __LINE__);
        $xmlresp = simplexml_load_string($resp);

        foreach ($xmlresp->Contents as $c) {
          $retval[(string)$c->Key] = basename((string)$c->Key);
          $lastMarker = (string)$c->Key;
        }

      } while ((string)$xmlresp->IsTruncated == 'true');

      fclose($fp);
      return $retval;
    }

  ### PRIVATE


// Sending HTTP query and receiving, with trivial keep-alive support
function sendREST($fp, $q, $debug = false)
{
    if ($debug) echo "\nQUERY<<{$q}>>\n";

    fwrite($fp, $q);
    $r = '';
    $check_header = true;
    while (!feof($fp)) {
        $tr = fgets($fp, 256);
        if ($debug) echo "\nRESPONSE<<{$tr}>>"; 
        $r .= $tr;

        if (($check_header)&&(strpos($r, "\r\n\r\n") !== false))
        {
            // if content-length == 0, return query result
            if (strpos($r, 'Content-Length: 0') !== false)
                return $r;
        }

        // Keep-alive responses does not return EOF
        // they end with \r\n0\r\n\r\n string
        if (substr($r, -7) == "\r\n0\r\n\r\n")
            return $r;
    }
    return $r;
}

// Sending HTTP query, without keep-alive support
function downloadREST($fp, $q)
{
    // opening HTTP connection to Amazon S3
    // since there is no keep-alive we open new connection for each request 
    $fp = fsockopen("s3.amazonaws.com", 80, $errno, $errstr, 30);
    if (!$fp) die("$errstr ($errno)\n"); // connection failed, pity 
        
    fwrite($fp, $q); // sending queyr
    $r = ''; // buffer for result 
    while (!feof($fp)){
      $line = fread($fp, 8192);
      if (!feof($fp)){
        $r .= $line;
      }
    }
    fclose($fp);
    
    return explode("\r\n\r\n", $r, 2);
    // returning HTTP response header and retrieved data 
}


// hmac-sha1 code START
// hmac-sha1 function:  assuming key is global $aws_secret 40 bytes long
// read more at http://en.wikipedia.org/wiki/HMAC
// warning: key($aws_secret) is padded to 64 bytes with 0x0 after first function call 
function amazon_hmac($stringToSign) 
{
    // helper function binsha1 for amazon_hmac (returns binary value of sha1 hash)
    if (!function_exists('binsha1'))
    { 
        if (version_compare(phpversion(), "5.0.0", ">=")) { 
            function binsha1($d) { return sha1($d, true); }
        } else { 
            function binsha1($d) { return pack('H*', sha1($d)); }
        }
    }

    $aws_secret = $this->GetPref('secretaccesskey');

    if (strlen($aws_secret) == 40)
        $aws_secret = $aws_secret.str_repeat(chr(0), 24);

    $ipad = str_repeat(chr(0x36), 64);
    $opad = str_repeat(chr(0x5c), 64);

    $hmac = binsha1(($aws_secret^$opad).binsha1(($aws_secret^$ipad).$stringToSign));
    return base64_encode($hmac);
}
// hmac-sha1 code END 



  }
?>
