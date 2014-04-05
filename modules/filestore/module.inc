<?php

/**
 * A fileStore can store and retrieve files, it also makes certain
 * files accessible for download via a URL
 *
 * Generally, you will update the photo object and then call the functions.
 * @author William Entriken <cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2014 William Entriken
 */
abstract class FileStore extends CameraLifeModule
{
    /**
     * Gets a URL for the client to access the selected resource. Or return FALSE.
     * If FALSE, the caller must construct a URL to media.php which will
     * getFile() the file and proxy it to the user.
     *
     * (Implementation note: make sure non-public photos do not have
     * publicly accessible urls)
     *
     * @param $bucket - one of ('photo', 'other')
     * @param $path - the location of the stored file, starts with '/'
     * @return mixed URL or FALSE
     */
    abstract public function getURL($bucket, $path);

    /**
     * Gets a local filename for the requested resource. It is downloaded if necessary.
     *
     * @param $bucket - one of ('photo', 'other')
     * @param $path - the location of the stored file, starts with '/'
     * @return array with these elements:
     *     filename - string - the local file you requested
     *     temporary - boolean
     *     mtime - unix time that this file was modified
     *
     * If temporary is TRUE, the caller is responsible for deleting this file when done.
     */
    abstract public function getFile($bucket, $path);

    /**
     * Save a file to the fileStore
     *
     * @param $bucket - one of ('photo', 'other')
     * @param $path - the location of the stored file, starts with '/'
     * @param $file - the local file which is to be put in the store
     * @param $secure - if secure, ensure this file will not be publicly accessible
     */
    abstract public function putFile($bucket, $path, $file, $secure = 0);

    /**
     * @param $bucket one of ('photo', 'other')
     * @param $path the location of the stored file, starts with '/'
     * @param $secure if secure, ensure this file will not be publicly accessible
     */
    abstract public function setPrivacy($bucket, $path, $secure);

    /**
     * erase a file
     *
     * @param $bucket - one of ('photo', 'other')
     * @param $path - the location of the stored file, starts with '/'
     */
    abstract public function eraseFile($bucket, $path);

    /**
     * Returns a list of all files in the fileStore
     *
     * @param $bucket - one of ('photo', 'other')
     * @param $path - the location of the stored file, starts with '/'
     * @param $recursive - whether to list recursively
     * @return arry of files, in the form 'path'=>basename(path)
     */
    abstract public function listFiles($bucket, $path = '', $recursive = true);
}