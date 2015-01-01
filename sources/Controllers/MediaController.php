<?php
namespace CameraLife\Controllers;
use CameraLife\Models as Models;

/**
 * Retrieve a photo from the FileStore and feed it to the user
 * This file makes asset security possible since the user does not directly access the photos.
 *
 * This gets linked to from Photo::getMedia() when a FileStore::getUrl() returns FALSE
 * You should understand that before continuing.
 *
 * Required GET variables
 * <ul>
 *  <li>id</li>
 *  <li>scale - ('photo', 'thumbnail', or 'scaled')</li>
 *  <li>ver (mtime)</li>
 * </ul>
 *
 * @author    William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
 * @access    public
 */

class MediaController extends Controller
{
    public function handleGet($get, $post, $files, $cookies)
    {
        $photo = Models\Photo::getPhotoWithID($get['id']);
        $format = isset($get['scale']) ? $get['scale'] : (isset($get['size']) ? $get['size'] : 'NOSIZE');
        if (!is_numeric($_GET['ver'])) {
            $cameralife->error('Required number ver missing! Query string: ' . htmlentities($_SERVER['QUERY_STRING']));
        }

        $extension = $photo->extension;

        if ($photo->get('status') != 0) {
//todo setup security
            if (!$cameralife->security->authorize('admin_file')) {
                $reason = null;
                if ($photo->get('status') == 1) {
                    $reason = "deleted";
                } elseif ($photo->get('status') == 2) {
                    $reason = "marked as private";
                } elseif ($photo->get('status') == 3) {
                    $reason = "uploaded but not revied";
                } elseif ($photo->get('status') == !0) {
                    $reason = "marked non-public";
                }
                if ($reason) {
                    $cameralife->error("Photo access denied: $reason");
                }
            }
        }

        $bucket = 'other';
        $path = '';

        if ($format == 'photo' || $format == '') {
            if ($photo->get('modified')) {
                $path = '/' . $photo->get('id') . '_mod.' . $extension;
            } else {
                $bucket = 'photo';
                $path = rtrim('/' . ltrim($photo->get('path'), '/'), '/') . '/' . $photo->get('filename');
            }
        } elseif ($format == 'scaled') {
            $thumbSize = Models\Preferences::valueForModuleWithKey('CameraLife', 'scaledsize');
            $path = "/{$photo->get('id')}_{$thumbSize}.{$extension}";
        } elseif ($format == 'thumbnail') {
            $thumbSize = Models\Preferences::valueForModuleWithKey('CameraLife', 'thumbsize');
            $path = "/{$photo->get('id')}_{$thumbSize}.{$extension}";
        } elseif (is_numeric($format)) {
            $valid = preg_split('/[, ]+/', Models\Preferences::valueForModuleWithKey('CameraLife', 'optionsizes'));
            if (in_array($format, $valid)) {
                $path = "/{$photo->get('id')}_{$format}.{$extension}";
            } else {
                $cameralife->error('This image size has not been allowed');
            }
        } else {
            $cameralife->error('Bad size parameter. Query string: ' . htmlentities($_SERVER['QUERY_STRING']));
        }

        $fileStore = Models\FileStore::fileStoreWithName($bucket);
        list($file, $temp, $mtime) = $fileStore->getFile($path);

        if (!$file) {
            $photo->generateThumbnail();
//todo fix
            list($file, $temp, $mtime) = $cameralife->fileStore->getFile($bucket, $filepath);
        }

        if ($extension == 'jpg' || $extension == 'jpeg') {
            header('Content-type: image/jpeg');
        } elseif ($extension == 'png') {
            header('Content-type: image/png');
        } elseif ($extension == 'gif') {
            header('Content-type: image/gif');
        } else {
            $cameralife->error('Unknown file type');
        }

        header('Content-Disposition: inline; filename="' . htmlentities($photo->get('description')) . '.' . $extension . '";');
        header('Content-Length: ' . filesize($file));
        header("Date: " . gmdate("D, d M Y H:i:s", $mtime) . " GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", $mtime) . " GMT");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + 2592000) . " GMT"); // One month

        if ($file) {
            readfile($file);
        }
        if ($temp) {
            unlink($file);
        }
    }
}
