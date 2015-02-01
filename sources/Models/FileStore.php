<?php
namespace CameraLife\Models;

/**
 * A fileStore can store and retrieve files, it also makes certain
 * files accessible for download via a URL
 *
 * An implementation of FileStore that stores photos on your local filesystem
 * An example path is: '/folder/file.png', all paths start with '/'
 *
 * @see FileStore
 *
 * @author    William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
 * @access    public
 */
class FileStore
{
    /**
     * baseDir
     *
     * @var string
     * @access private
     */
    private $baseDir;

    /**
     * __construct function.
     *
     * @access private
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * fileStoreWithName function.
     *
     * @access public
     * @param string $name -- 'photo' or 'other'
     * @return void
     */
    public static function fileStoreWithName($name)
    {
        $retval = new FileStore;
        $path = NULL;

        if ($name == 'photo') {
            $path = Preferences::valueForModuleWithKey('LocalFileStore', 'photo_dir');
        } else if ($name = 'other') {
            $path = Preferences::valueForModuleWithKey('LocalFileStore', 'cache_dir');
        } else {
            throw new \Exception('Bad FileStore name');
        }
        if (!realpath($path)) {
            throw new \Exception('FileStore path does not exist for ' . $path);
        }
        $retval->baseDir = realpath($path);

        return $retval;
    }

    /**
     * Gets a URL for the client to access the selected resource. Or return FALSE.
     * If FALSE, the caller must construct a URL to media.php which will
     * getFile() the file and proxy it to the user.
     *
     * (Implementation note: make sure non-public photos do not have
     * publicly accessible urls)
     *
     * @param  $path - the location of the stored file, starts with '/'
     * @return mixed URL or FALSE
     */
    public function getUrl($path)
    {
        return false;
    }

    /**
     * Gets a local filename for the requested resource. It is downloaded if necessary.
     *
     * @param  $path - the location of the stored file, starts with '/'
     * @return mixed
     *   array with these elements:
     *     filename - string - the local file you requested
     *     temporary - boolean
     *     mtime - unix time that this file was modified
     *   or null
     *
     * If temporary is TRUE, the caller is responsible for deleting this file when done.
     */
    public function getFile($path)
    {
        $fullpath = $this->baseDir . $path;
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
    public function putFile($path, $file, $secure = 0)
    {
        $fullpath = $this->baseDir . $path;
        if (!is_dir(dirname($fullpath))) {
            mkdir(dirname($fullpath), 0777, true);
        }
        rename($file, $fullpath);
    }

    /**
     * erase a file
     *
     * @param $bucket - one of ('photo', 'other')
     * @param $path - the location of the stored file, starts with '/'
     *
     * @return none
     */
    public function eraseFile($path)
    {
        unlink($this->baseDir . $path);
    }

//todo: should return file sizes
    /**
     * Returns a list of all files in the fileStore
     *
     * @param  $bucket - one of ('photo', 'other')
     * @param  $path - the location of the stored file, starts with '/'
     * @param  $recursive - whether to list recursively
     * @return array - files, in the form 'path'=>basename(path)
     */
    public function listFiles($path = '/', $recursive = true)
    {
        $fullpath = $this->baseDir . $path;

        #FEATURE // add " NOCL" to the end of a folder to exclude it
        if (stripos($path, ' NOCL') !== false) {
            return array();
        }

        if (is_file($fullpath)) {
            return array($fullpath => basename($fullpath));
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
                $retval += $this->listFiles($child, true);
            }
        } else {
            return null;
        }
        return $retval;
    }
}
