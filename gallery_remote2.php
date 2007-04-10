<?php

  # An implementation of the Gallery Remote API version 2.3
  # http://svn.sourceforge.net/viewvc/*checkout*/gallery/trunk/gallery_remote/gal_remote_proto-2.html

  $features=array('database', 'security', 'imageprocessing');
  require "main.inc";

  # gallery remote api is inadequate!
  if ($_GET['redirect'])
  {
    list($photoid, $type) = split('XXX', $_GET['redirect']);
    $photo = new Photo($photoid);
    header('Location: '.html_entity_decode($photo->GetMedia($type)));
    exit(0);
  }

  function mkdir_p($path)
  {
    return is_dir($path) || mkdir_p(dirname($path)) && mkdir($path);
  }

  # hoping noone uses that string normally
  function escapeforgr($string)
  {
    $escape = '1q2w3e';
//     $string = str_replace(' ', "${escape}space", $string);
//     $string = str_replace('/', "${escape}slash", $string);
//     $string = str_replace('\\', "${escape}backspash", $string);
//     $string = str_replace('\'', "${escape}quote", $string);
//     $string = str_replace('&', "${escape}amp", $string);
//     $string = str_replace(' ', "${escape}space", $string);


    $string = urlencode($string);
    $string = str_replace('+', '%20', $string);
    return $string;
  }
  function unescapeforgr($string)
  {
    $escape = '1q2w3e';
//    $string = str_replace('1q2w3e4rslash','/', $string);
    $string = str_replace('%20', '+', $string);
    $string = urldecode($string);

    return $string;
  }

#  header('Content-type: text/plain');

  switch($_POST['cmd'])
  {
  case 'login':
    gr_login();
    break;
  case 'fetch-album-images':
    gr_fetch_album_images();
    break;
  case 'fetch-albums';
  case 'fetch-albums-prune';
    gr_fetch_albums();
    break;
  case 'add-item';
    gr_add_item();
    break;
  case 'album-properties';
    gr_album_properties();
    break;
  case 'new-album';
    gr_new_album();
    break;
  default:
    echo "#__GR2PROTO__\n";
    echo "status=301\n";
    echo "status_text=Not supported: ".$_POST['cmd']."\n";
    break;
  }

  function gr_login()
  {
    global $cameralife;
    $result = $cameralife->Security->Login($_POST['uname'], $_POST['password']);
    setcookie('GALLERYSID', $_COOKIE[$cameralife->preferences['defaultsecurity']['auth_cookie']], time()+60*60*24*30);

    if ($result === true)
    {
      echo "#__GR2PROTO__\n";
      echo "status=0\n";
      echo "status_text=Login successful.\n";
      echo "server_version=2.6\n";
    }
    else
    {
      echo "#__GR2PROTO__\n";
      echo "status = 201\n";
      echo "status_text = Login failed.\n";
      echo "server_version = 2.6\n";
    }
  }

  function gr_fetch_albums()
  {
/*$r = print_r($GLOBALS, 1);
$fd = fopen('tmp','w+');
fwrite($fd, $r);
fclose($fd);*/
    global $cameralife;

    if (!$cameralife->Security->Authorize('admin_file'))
    {
      echo "status=401\n"; #close
      echo "status_text=You do not have permission to do that.\n";
      exit(1);
    }


    echo "#__GR2PROTO__\n";

    $folders = folder_search(new Folder(''));
    foreach ($folders as $i => $folder)
    {
      $parentname = dirname($folder);
      if ($parentname == '.') $parentname = '';
      if ($parentname) $parentname .= '/';
      if (!$parentname)
        $parent = 0;
      else
        $parent = $parentname;
//        $parent = array_search($parentname, $folders);

      if ($parent === FALSE) die('nest error!');

      echo "album.name.$i=".escapeforgr($folder)."\n";
      echo "album.title.$i=".basename($folder)."\n";
      echo "album.parent.$i=".escapeforgr($parent)."\n";
      echo "album.perms.add.$i=true\n";
      echo "album.perms.write.$i=true\n";
      echo "album.perms.del_items.$i=true\n";
      echo "album.perms.del_alb.$i=true\n";
      echo "album.perms.create_sub.$i=true\n";
      echo "album.perms.write.$i=true\n";
      echo "album.info.extrafields.$i=none\n";
    }
    echo "album_count=".count($folders)."\n";
    echo "status=0\n"; #close
    echo "status_text=Fetch-albums successful.\n";
  }

  function gr_fetch_album_images()
  {
    global $cameralife;

    echo "#__GR2PROTO__\n";

    $folder = new Folder(unescapeforgr($_POST['set_albumName']));
    $i=1;

    foreach ($folder->GetPhotos() as $photo)
    {
#      echo "image.name.$i=".$photo->GetMedia('photo')."\n";
      echo "image.name.$i=".$photo->Get('id')."XXXphoto\n";
      echo "image.raw_width.$i=".$photo->Get('width')."\n";
      echo "image.raw_height.$i=".$photo->Get('height')."\n";
      echo "image.raw_filesize.$i=".$photo->Get('fsize')."\n";

#      echo "image.resizedName.$i=".$photo->GetMedia('scaled')."\n";
      echo "image.resizedName.$i=".$photo->Get('id')."XXXscaled\n";
      #echo "image.resized_width.$i=".$photo->Get('width')."\n";
      #echo "image.resized_height.$i=".$photo->Get('height')."\n";

#      echo "image.thumbName.$i=".$photo->GetMedia('thumb')."\n";
      echo "image.thumbName.$i=".$photo->Get('id')."XXXthumbnail\n";
      echo "image.thumb_width.$i=".$photo->Get('tn_width')."\n";
      echo "image.thumb_height.$i=".$photo->Get('tn_height')."\n";

      echo "image.caption.$i=".$photo->Get('description')."\n";
      echo "image.clicks.$i=".$photo->Get('hits')."\n";

      #echo "image.forceExtension.$i=".$photo->Get('filename')."\n";
      #echo "image.hidden.$i=".($photo->Get('status')>0?'true':'false')."\n";
      $i++;
    }
    echo "baseurl=".$cameralife->base_url."/gallery_remote2.php?redirect=\n";
    echo "image_count=".($i-1)."\n";
    echo "status=0\n"; #close
    echo "status_text=Fetch-album-images successful.\n";
  }

  function folder_search($folder, $i=1  )
  {
    $retval = array();
    if ($folder->Path())
        $retval[$i++] = $folder->Path();
    foreach ($folder->GetChildren() as $child)
    {
      $result = folder_search($child, $i);
      $i += count($result);
      $retval += $result;
    }

    return $retval;
  }

  function gr_add_item()
  {
    global $cameralife;

    $path = unescapeforgr($_POST['set_albumName']);
    #userfile=user-file
    $filename = $_POST['userfile_name'];
    if ($_FILES['userfile']['name'])
      $filename = $_FILES['userfile']['name'];
    if ($_POST['force_filename'])
      $filename = $_POST['force_filename'];
    $description = $_POST['caption'];
    #extrafield.fieldname=fieldvalue [optional, since 2.3]

    if ($_FILES) 
    {
      $condition = "filename='".$filename."' and fsize=".$_FILES['userfile']['size'];
      $cameralife->Database->SelectOne('photos','COUNT(*)',$condition)
        and $error = "A photo with the same name and size already exists. Please rename and try again, or stop uploading duplicate photos.";

      if (eregi('/',$_FILES['userfile']['name']))
        $error = "It appears you are hacking, that is disallowed.";

      if ($_FILES['userfile']['size'] < 4096)
        $error = "The file is too small, minimum size is 4kb";

      if ($_FILES['userfile']['error'] == UPLOAD_ERR_INI_SIZE)
        $error = "The file was too big for the server.";

      if ($_FILES['userfile']['error'] == UPLOAD_ERR_PARTIAL)
        $error = "The file was only partially uploaded.";

      if ($_FILES['userfile']['error'] == UPLOAD_ERR_NO_FILE)
        $error = "No file was selected for upload.";

      if (!file_exists($cameralife->preferences['core']['photo_dir'].'/'.$path))
      {
        mkdir_p($cameralife->preferences['core']['photo_dir'].'/'.$path)
          or $error = "The selected upload directory doesn't exist and could not be created.";
      }

      if (!is_writable($cameralife->preferences['core']['photo_dir'].'/'.$path))
        $error = "The selected upload directory isn't writable.";

      if (!$error)
      {
        move_uploaded_file($_FILES['userfile']['tmp_name'], 
                          $cameralife->preferences['core']['photo_dir'].'/'.$path.$filename)
          or $cameralife->Error("Could not upload the photo, is the destination writable?");

        $upload['filename'] = $filename;
        $upload['path'] = $path;
        $upload['description'] = $description;
        $upload['username'] = $cameralife->Security->GetName();
        $upload['status'] = 0;

        $photo = new Photo($upload);
      }
    }
    else
    {
      $error = 'Photo not received.';
    }

    if ($error)
    {
      echo "#__GR2PROTO__\n";
      echo "status=403\n";
      echo "status_text=$error\n";
    }
    else
    {
      echo "#__GR2PROTO__\n";
      echo "status=0\n";
      echo "status_text=Upload successful.\n";
    }
  }

  function gr_album_properties()
  {
    echo "#__GR2PROTO__\n";
    echo "status=0\n";
    echo "status_text=Camera Life keeps photos at your original size.\n";
    echo "auto_resize=0\n";
  }

  function gr_new_album()
  {
    echo "#__GR2PROTO__\n";
    echo "status=0\n";
    echo "status_text=Camera Life does not support empty folders (albums), upload where you want, We'll make the folder on the fly.\n";
    echo "album_name=".$_POST['set_albumName'].$_POST['newAlbumName'].escapeforgr('/')."\n";

  }
?>
