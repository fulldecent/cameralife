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
    /**
     * getFileForPhotoWithScale function.
     * 
     * @access private
     * @param Models\Photo $photo
     * @param mixed $scale
     * @return [$file, $temp, $mtime]
     */
    private static function getFileForPhotoWithScale(Models\Photo $photo, $scale)
    {
        $extension = $photo->extension;
        $bucket = 'other';
        $path = '';

        if ($scale == 'photo') {
            if ($photo->get('modified')) {
                $path = '/' . $photo->get('id') . '_mod.' . $extension;
            } else {
                $bucket = 'photo';
                $path = rtrim('/' . ltrim($photo->get('path'), '/'), '/') . '/' . $photo->get('filename');
            }
        } elseif ($scale == 'scaled') {
            $thumbSize = Models\Preferences::valueForModuleWithKey('CameraLife', 'scaledsize');
            $path = "/{$photo->get('id')}_{$thumbSize}.{$extension}";
        } elseif ($scale == 'thumbnail') {
            $thumbSize = Models\Preferences::valueForModuleWithKey('CameraLife', 'thumbsize');
            $path = "/{$photo->get('id')}_{$thumbSize}.{$extension}";
        } elseif (is_numeric($scale)) {
            $valid = preg_split('/[, ]+/', Models\Preferences::valueForModuleWithKey('CameraLife', 'optionsizes'));
            if (!in_array($scale, $valid)) {
                throw new \Exception('This image size has not been allowed');
            }
            $path = "/{$photo->get('id')}_{$scale}.{$extension}";
        } else {
            throw new \Exception('Missing or bad size parameter');
        }

        $fileStore = Models\FileStore::fileStoreWithName($bucket);
        list($file, $temp, $mtime) = $fileStore->getFile($path);

        if (!$file) {
            $photo->generateThumbnail();
            list($file, $temp, $mtime) = $fileStore->getFile($path);
        }
        return [$file, $temp, $mtime];
    }
  
    public function handleGet($get, $post, $files, $cookies)
    {
        $photo = Models\Photo::getPhotoWithID($get['id']);
        $scale = isset($get['scale']) ? $get['scale'] : null;
        $extension = $photo->extension;
        if (!is_numeric($get['ver'])) {
            throw new \Exception('Required number ver missing! Query string: ' . htmlentities($_SERVER['QUERY_STRING']));
        }
        if ($photo->get('status') != 0) {
            if (Models\User::currentUser($cookies)->authorizationLevel < 5) {
                throw new \Exception('Photo access denied');
            }
        }
        list($file, $temp, $mtime) = self::getFileForPhotoWithScale($photo, $scale);

        if ($extension == 'jpg' || $extension == 'jpeg') {
            header('Content-type: image/jpeg');
        } elseif ($extension == 'png') {
            header('Content-type: image/png');
        } elseif ($extension == 'gif') {
            header('Content-type: image/gif');
        } else {
            throw new \Exception('Unknown photo type');
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
