<?php
  # Handle the POST form action from upload.php
  #
  # Pass me variables:
  # path = the upload path
  # description = the photo(s) description
  # userfile = encoded file to upload, JPG or ZIP
  # target = the exit URL, or 'ajax' for an ajax call

  @ini_set('max_execution_time',9000);
  $features = array('database', 'security', 'imageprocessing', 'theme');
  require 'main.inc';

  // Description: Adds a file to the system
  // Precondition: the images exists at $cameralife->preferences['core']['photo_dir'].'/'.$path.$filename
  // Postcondition: image is added to database, thumbnails created
  // Return: 0 = success, or a string describing the error
  function add_image($path, $filename, $description = 'unnamed', $status = 0)
  {
    global $cameralife;

    $filesize = filesize($cameralife->preferences['core']['photo_dir'].'/'.$path.$filename);

    $exists = $cameralife->Database->SelectOne('photos','COUNT(*)',"filename='$filename' AND fsize=$filesize");
    if ($exists)
      return "The photo <b>$filename</b> is already in the system. This photo was skipped from uploading.";

    $upload['filename'] = $filename;
    $upload['path'] = $path;
    $upload['description'] = $description;
    $upload['username'] = $cameralife->Security->GetName();
    $upload['status'] = $status;

    $photo = new Photo($upload);

    return 0;
  }


  if (isset($_REQUEST['path']) && $_REQUEST['path'] != 'upload/'.$user['username'].'/')
  {
    $cameralife->Security->Authorize('admin_file', 1);
    $path = $_REQUEST['path'];
  }
  else
  {
    $cameralife->Security->Authorize('photo_upload', 1);
    $path = 'upload/'.$cameralife->Security->GetName().'/';
  }

  /* Bonus code:
     use this line to make user uploads be reviewed by an admin
     before they go live. To see them, Administration->Files->Uploads
  */
  //$status = $cameralife->Security->authorize('admin_file') ? 0 : 3;
  $status = 0;

  if (!$_FILES)
    $cameralife->Error('No file was uploaded.', __FILE__, __LINE__);

  $condition = "filename='".$_FILES['userfile']['name']."'";
  $cameralife->Database->SelectOne('photos','COUNT(*)',$condition)
    and $cameralife->Error("The filename \"".$_FILES['userfile']['name']."\" is already used in system. Please rename the image and try uploading again.");

  if (eregi('/',$_FILES['userfile']['name']))
    $cameralife->Error("It appears you are hacking, that is disallowed.", __FILE__, __LINE__);

  if ($_FILES['userfile']['size'] < 4096)
    $cameralife->Error("The file is too small, minimum size is 4kb", __FILE__);

  if ($_FILES['userfile']['error'] == UPLOAD_ERR_INI_SIZE)
    $cameralife->Error("The file was too big for the server.", __FILE__);

  if ($_FILES['userfile']['error'] == UPLOAD_ERR_PARTIAL)
    $cameralife->Error("The file was only partially uploaded.", __FILE__);

  if ($_FILES['userfile']['error'] == UPLOAD_ERR_NO_FILE)
    $cameralife->Error("No file was selected for upload.", __FILE__);

  if (!file_exists($cameralife->preferences['core']['photo_dir'].'/'.$path))
  {
    mkdir($cameralife->preferences['core']['photo_dir'].'/'.$path)
      or $cameralife->Error("The selected upload directory doesn't exist and could not be created.", __FILE__, __LINE__);
  }

  if (!is_writable($cameralife->preferences['core']['photo_dir'].'/'.$path))
    $cameralife->Error("The selected upload directory isn't writable.", __FILE__, __LINE__);

  if (eregi ('\.zip$', $_FILES['userfile']['name']))
  {
    //echo "Uploading ZIP file.<br>";
    $extractionpath = $cameralife->preferences['core']['photo_dir'].'/'.$path;
    $basename = $_FILES['userfile']['name'];
    move_uploaded_file($_FILES['userfile']['tmp_name'], $extractionpath.$basename)
      or $camerlife->Error("Could not move the zip file, is the destination writable?");

    exec ("unzip -d '$extractionpath' -nj '$extractionpath$basename' '*jpg' '*JPG' '*jpeg' '*JPEG' '*png' '*PNG'", $output, $return);
    unlink ($extractionpath.$basename);

    foreach ($output as $outputline)
    {
      $outputline = explode($extractionpath, $outputline);
      if (count($outputline) != 2) continue;
      if (!preg_match("/.jpg$|.jpeg$|.png$/i", $outputline[1])) continue;
        $result = add_image($path, $outputline[1], $_POST['description'], $status);

      if ($result)
      {
        $cameralife->Error("Filename: ".$outputline[1], __FILE__);
#TODO delete the files on failure
      }
    }
  }
  else
  {
    move_uploaded_file($_FILES['userfile']['tmp_name'], 
                      $cameralife->preferences['core']['photo_dir'].'/'.$path.$_FILES['userfile']['name'])
      or $camerlife->Error("Could not upload the photo, is the destination writable?");

    $result = add_image($path, $_FILES['userfile']['name'], $_POST['description'], $status);

    if ($result != 0)
    {
      $cameralife->Error("Error adding image: $result", __FILE__);
    }
  }

  if ($_POST['target'] == 'ajax')
    exit(0);
  else
    header("Location: ".$_POST['target']);
?>
