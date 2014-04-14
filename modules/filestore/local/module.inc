<?php

/**
 * An implementation of FileStore that stores photos on your local filesystem
 * An example path is: '/folder/file.png', all paths start with '/'
 * @see FileStore
 *
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2014 William Entriken
 * @access public
 */
class LocalFileStore extends FileStore
{
    public function __construct()
    {
        global $cameralife;

        $this->name = 'Local';

        $this->preferences[] = array(
            'name' => 'photo_dir',
            'desc' => 'Main photo directory',
            'type' => 'directory',
            'default' => 'images/photos'
        );
        $this->preferences[] = array(
            'name' => 'cache_dir',
            'desc' => 'Data directory',
            'type' => 'directoryrw',
            'default' => 'images/cache'
        );

        $prevdir = getcwd();
        chdir($cameralife->baseDir);
        $this->photoDir = rtrim(realpath($this->getPref('photo_dir')), '/');
        $this->cacheDir = rtrim(realpath($this->getPref('cache_dir')), '/');
        chdir($prevdir);
    }

    /**
     * getURL
     * We don't not have special URLs to access the photos.
     * The parent class defaults to returning FALSE.
     */
    public function getURL($bucket, $path){
        return false;
    }

    /**
     * Gets a local filename for the requested resource. It is downloaded if necessary.
     *
     * @param $bucket - one of ('photo', 'other')
     * @param $path - the location of the stored file, starts with '/'
     * @return mixed
     *   array with these elements:
     *     filename - string - the local file you requested
     *     temporary - boolean
     *     mtime - unix time that this file was modified
     *   or null
     *
     * If temporary is TRUE, the caller is responsible for deleting this file when done.
     */
    public function getFile($bucket, $path)
    {
        global $cameralife;
        $fullpath = '';
        if ($bucket == 'photo') {
            $fullpath = $this->photoDir . $path;
        } else {
            if ($bucket == 'other') {
                $fullpath = $this->cacheDir . $path;
            } else {
                $cameralife->error("The FileStore bucket <b>$bucket</b> is invalid");
            }
        }
        if (!file_exists($fullpath)) {
            return false;
        }
        return array($fullpath, false, filemtime($fullpath));
    }

    /**
     * Save a file to the fileStore
     *
     * @param $bucket - one of ('photo', 'other')
     * @param $path - the location of the stored file
     * @param $file - the local file which is to be put in the store
     * @param $secure - if secure, ensure this file will not be publicly accessible
     */
    public function putFile($bucket, $path, $file, $secure = 0)
    {
        global $cameralife;
        $fullpath = '';
        if ($bucket == 'photo') {
            $fullpath = $this->photoDir . $path;
        } else {
            if ($bucket == 'other') {
                $fullpath = $this->cacheDir . $path;
            } else {
                $cameralife->error("The FileStore bucket <b>$bucket</b> is invalid");
            }
        }

        @mkdir(dirname($fullpath, 0666, true));
        rename($file, $fullpath);
    }

    /**
     * @param string $bucket one of ('photo', 'other')
     * @param string $path the location of the stored file
     * @param string $secure if secure, ensure this file will not be publicly accessible
     */
    public function setPrivacy($bucket, $path, $secure)
    {
        global $cameralife;
        $cameralife->error('FEATURE MISSING: PRIVACY');
    }

    /**
     * erase a file
     *
     * @param $bucket - one of ('photo', 'other')
     * @param $path - the location of the stored file, starts with '/'
     *
     * @return none
     */
    public function eraseFile($bucket, $path)
    {
        global $cameralife;
        $fullpath = '';
        if ($bucket == 'photo') {
            $fullpath = $this->photoDir . $path;
        } else {
            if ($bucket == 'other') {
                $fullpath = $this->cacheDir . $path;
            } else {
                $cameralife->error("The FileStore bucket <b>$bucket</b> is invalid");
            }
        }
        unlink($fullpath);
    }

    /**
     * Returns a list of all files in the fileStore
     *
     * @param $bucket - one of ('photo', 'other')
     * @param $path - the location of the stored file, starts with '/'
     * @param $recursive - whether to list recursively
     * @return an array of files, in the form 'path'=>basename(path)
     */
    public function listFiles($bucket, $path = '/', $recursive = true)
    {
        global $cameralife;
        $fullpath = '';
        if ($bucket == 'photo') {
            $fullpath = $this->photoDir . $path;
        } else {
            if ($bucket == 'other') {
                $fullpath = $this->cacheDir . $path;
            } else {
                $cameralife->error("The FileStore bucket <b>$bucket</b> is invalid");
            }
        }

        #FEATURE // add " NOCL" to the end of a folder to exclude it
        if (strpos($path, ' NOCL') !== false) {
            return array();
        }

        $retval = array();
        if ($dir = @opendir($fullpath)) {
            $children = array();
            while (false !== ($file = readdir($dir))) {
                if ($file[0] == '.') {
                    continue;
                }
                if (is_file($fullpath . $file)) {
                    $retval[$path . $file] = $file;
                } else {
                    if ($recursive && is_dir($fullpath . $file)) {
                        $children[] = $path . $file . '/';
                    }
                }
            }
            closedir($dir);
            sort($children);
            foreach ($children as $child) {
                $retval += $this->listFiles($bucket, $child, true);
            }
        } else {
            $cameralife->error('Failed to open file directory');
        }
        return $retval;
    }
}