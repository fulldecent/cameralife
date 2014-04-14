<?php
/**
 * Handles the POST form action from upload.php
 * Pass the following variables
 * <ul>
 * <li>path = the upload path</li>
 * <li>description = the photo(s) description</li>
 * <li>userfile = encoded file to upload, JPG or ZIP</li>
 * <li>target = the exit URL, or 'ajax' for an ajax call</li></ul>
 * @author William Entriken <cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2001-2009 William Entriken
 */

@ini_set('max_execution_time', 9000);
$features = array('security', 'imageProcessing', 'theme', 'fileStore');
require 'main.inc';

/**
 * Adds a file to the system
 * Precondition - the images exists at $file
 * Postcondition: image is added to the fileStore at $path$filename
 *
 * @param $path
 * @param $filename
 * @param $file
 * @param string $description
 * @param int $status
 * @return int|string Zero or string describing error
 */
function add_image($path, $filename, $file, $description = 'unnamed', $status = 0)
{
    global $cameralife;

    if (strpos(mime_content_type($file), 'image/') != 0) {
        $cameralife->Error("Invalid mimetype for uploaded file");
    }

    if (preg_match('/\.\./', $path)) {
        $cameralife->Error("Invalid path for uploaded file");
    }

    if (!$description) {
        $description = 'unnamed';
    }
    $filesize = filesize($file);

    $exists = $cameralife->database->SelectOne('photos', 'COUNT(*)', "filename='$filename' AND fsize=$filesize");
    if ($exists) {
        return "The photo <b>$filename</b> is already in the system. This photo was skipped from uploading.";
    }

    $im = @imagecreatefromstring(file_get_contents($file));
    $valid = ($im !== false);
    @imagedestroy($im);
    if (!$valid) {
        return "Not a valid image file.";
    }

    $upload['filename'] = $filename;
    $upload['path'] = $path;
    $upload['description'] = $description;
    $upload['username'] = $cameralife->security->getName();
    $upload['status'] = $status;

    $photo = new Photo($upload);
    $filepath = rtrim('/' . ltrim($upload['path'], '/'), '/') . '/' . $upload['filename'];
    $cameralife->fileStore->PutFile('photo', $filepath, $file);
    @unlink($file);
    return 0;
}

if (isset($_REQUEST['path']) && $_REQUEST['path'] != 'upload/' . $cameralife->security->getName() . '/') {
    $cameralife->security->Authorize('admin_file', 1);
    $path = $_REQUEST['path'];
} else {
    $cameralife->security->Authorize('photo_upload', 1);
    $path = '/upload/' . $cameralife->security->getName();
}

/* Bonus code:
   use this line to make user uploads be reviewed by an admin
   before they go live. To see them, Administration->Files->Uploads
*/
//$status = $cameralife->security->authorize('admin_file') ? 0 : 3;
$status = 0;

if (!$_FILES) {
    $cameralife->error('No file was uploaded.');
}

$condition = "filename='" . $_FILES['userfile']['name'] . "'";
$cameralife->database->SelectOne('photos', 'COUNT(*)', $condition)
and $cameralife->error(
    "The filename \"" . $_FILES['userfile']['name'] . "\" is already used in system. Please rename the image and try uploading again."
);

if (preg_match('|/|', $_FILES['userfile']['name'])) {
    $cameralife->error("It appears you are hacking, that is disallowed.");
}

if ($_FILES['userfile']['size'] < 4096) {
    $cameralife->error("The file is too small, minimum size is 4kb");
}

if ($_FILES['userfile']['error'] == UPLOAD_ERR_INI_SIZE) {
    $cameralife->error("The file was too big for the server.");
}

if ($_FILES['userfile']['error'] == UPLOAD_ERR_PARTIAL) {
    $cameralife->error("The file was only partially uploaded.");
}

if ($_FILES['userfile']['error'] == UPLOAD_ERR_NO_FILE) {
    $cameralife->error("No file was selected for upload.");
}

if (!function_exists('sys_get_temp_dir')) {
    // Based on http://www.phpit.net/
    // article/creating-zip-tar-archives-dynamically-php/2/
    /**@link http://www.phpit.net/article/creating-zip-tar-archives-dynamically-php/2/
     */

    function sys_get_temp_dir()
    {
        // Try to get from environment variable
        if (!empty($_ENV['TMP'])) {
            return realpath($_ENV['TMP']);
        } elseif (!empty($_ENV['TMPDIR'])) {
            return realpath($_ENV['TMPDIR']);
        } elseif (!empty($_ENV['TEMP'])) {
            return realpath($_ENV['TEMP']);
        } // Detect by creating a temporary file
        else {
            // Try to use system's temporary directory
            // as random name shouldn't exist
            $temp_file = tempnam(md5(uniqid(rand(), true)), '');
            if ($temp_file) {
                $temp_dir = realpath(dirname($temp_file));
                unlink($temp_file);

                return $temp_dir;
            } else {
                return false;
            }
        }
    }
}

if (preg_match('|\.zip$|i', $_FILES['userfile']['name'])) {
    //echo "Uploading ZIP file.<br>";
    $temp = tempnam('', 'cameralife_');
    $tempdir = sys_get_temp_dir();

    $basename = $_FILES['userfile']['name'];
    move_uploaded_file($_FILES['userfile']['tmp_name'], $temp)
    or $cameralife->Error("Could not move the zip file, is the destination writable? $temp");

    exec("unzip -d $tempdir -nj '$temp' '*jpg' '*JPG' '*jpeg' '*JPEG' '*png' '*PNG'", $output, $return);
    unlink($temp);

    foreach ($output as $outputline) {
        if (preg_match("|$tempdir" . '/?\s?(.+)|', $outputline, $matches)) {
            if (!preg_match("/.jpg$|.jpeg$|.png$/i", $matches[1])) {
                continue;
            }
            $result = add_image($path, $matches[1], $tempdir . '/' . $matches[1], $_POST['description'], $status);
            unlink($tempdir . '/' . $matches[1]);
        }

        if ($result) {
            $cameralife->error("Filename: " . $outputline[1], __FILE__);
        }
    }
} elseif (preg_match(':\.jpg$|\.png$|\.jpeg$:i', $_FILES['userfile']['name'])) {
    $temp = tempnam('', 'cameralife_');

    move_uploaded_file($_FILES['userfile']['tmp_name'], $temp)
    or $cameralife->Error("Could not upload the photo, is the destination writable?");

    $result = add_image($path, $_FILES['userfile']['name'], $temp, $_POST['description'], $status);
    @unlink($temp);

    if ($result) {
        $cameralife->error("error adding image: $result", __FILE__);
    }
} else {
    $cameralife->error('Unsupported filetype');
}

if ($_POST['target'] == 'ajax') {
    exit(0);
} else {
    header("Location: " . $_POST['target']);
}
