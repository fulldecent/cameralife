<?php
  # Handle the POST form action from upload.php
  #
  # Pass me variables:
  # path = the upload path
  # description = the photo(s) description
  # userfile = encoded file to upload, JPG or ZIP
  # target = the exit URL, or 'ajax' for an ajax call

  @ini_set('max_execution_time',9000);
  $features = array('database', 'security', 'imageprocessing', 'theme', 'photostore');
  require 'main.inc';

  // Description: Adds a file to the system
  // Precondition: the images exists at $file
  // Postcondition: image is added to the photostore at $path . $filename
  // Return: 0 = success; or a string describing the error
  function add_image($path, $filename, $file, $description = 'unnamed', $status = 0)
  {
    global $cameralife;

    if (!$description) $description = 'unnamed';
    $filesize = filesize($file);

    $exists = $cameralife->Database->SelectOne('photos','COUNT(*)',"filename='$filename' AND fsize=$filesize");
    if ($exists)
      return "The photo <b>$filename</b> is already in the system. This photo was skipped from uploading.";

    $upload['filename'] = $filename;
    $upload['path'] = $path;
    $upload['description'] = $description;
    $upload['username'] = $cameralife->Security->GetName();
    $upload['status'] = $status;

    $photo = new Photo($upload);
    $cameralife->PhotoStore->PutFile($photo, $file);
    unlink($file);

    return 0;
  }


  if (isset($_REQUEST['path']) && $_REQUEST['path'] != 'upload/'.$cameralife->Security->GetName().'/')
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


if ( !function_exists('sys_get_temp_dir') )
{
    // Based on http://www.phpit.net/
    // article/creating-zip-tar-archives-dynamically-php/2/
    function sys_get_temp_dir()
    {
        // Try to get from environment variable
        if ( !empty($_ENV['TMP']) )
        {
            return realpath( $_ENV['TMP'] );
        }
        else if ( !empty($_ENV['TMPDIR']) )
        {
            return realpath( $_ENV['TMPDIR'] );
        }
        else if ( !empty($_ENV['TEMP']) )
        {
            return realpath( $_ENV['TEMP'] );
        }

        // Detect by creating a temporary file
        else
        {
            // Try to use system's temporary directory
            // as random name shouldn't exist
            $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
            if ( $temp_file )
            {
                $temp_dir = realpath( dirname($temp_file) );
                unlink( $temp_file );
                return $temp_dir;
            }
            else
            {
                return FALSE;
            }
        }
    }
}

  if (eregi ('\.zip$', $_FILES['userfile']['name']))
  {
    //echo "Uploading ZIP file.<br>";
    $temp = tempnam('', 'cameralife_');
    $tempdir = sys_get_temp_dir();

    $basename = $_FILES['userfile']['name'];
    move_uploaded_file($_FILES['userfile']['tmp_name'], $temp)
      or $camerlife->Error("Could not move the zip file, is the destination writable? $temp");

    exec ("unzip -d $tempdir -nj '$temp' '*jpg' '*JPG' '*jpeg' '*JPEG' '*png' '*PNG'", $output, $return);
    unlink ($temp);

    foreach ($output as $outputline)
    {
      if (preg_match("|$tempdir".'/?\s?(.+)|', $outputline, $matches))
      {
        if (!preg_match("/.jpg$|.jpeg$|.png$/i", $matches[1])) continue;
        $result = add_image($path, $matches[1], $tempdir.'/'.$matches[1], $_POST['description'], $status);
        unlink($tempdir.'/'.$matches[1]);
      }

      if ($result)
      {
        $cameralife->Error("Filename: ".$outputline[1], __FILE__);
      }
    }
  }
  else
  {
    $temp = tempnam('', 'cameralife_');

    move_uploaded_file($_FILES['userfile']['tmp_name'], $temp)
      or $camerlife->Error("Could not upload the photo, is the destination writable?");

    $result = add_image($path, $_FILES['userfile']['name'], $temp, $_POST['description'], $status);
    @unlink ($temp);

    if ($result != 0)
      $cameralife->Error("Error adding image: $result", __FILE__);
  }

  if ($_POST['target'] == 'ajax')
    exit(0);
  else
    header("Location: ".$_POST['target']);
?>
