<?php
namespace CameraLife;

/**
 * Topic class.
 *
 * @author William Entriken <WillEntriken @gmail.com>
 * @access public
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @extends Search
 */
class Topic extends Search
{
    public $name;

    public function __construct($name)
    {
        parent::__construct();
        $this->name = $name;
        $this->mySearchAlbumCondition = "topic = :name";
        $this->mySearchPhotoCondition = "FALSE";
        $this->mySearchFolderCondition = "FALSE";
        $this->myBinds['name'] = $this->name;
    }

//TODO DEPRECATED?
    public function getName()
    {
        return htmlentities($this->name);
    }

    public function get($item)
    {
        return $this->$item;
    }

    public static function getTopics()
    {
        global $cameralife;
        $retval = array();
        $result = $cameralife->database->Select('albums', 'DISTINCT topic');
        while ($topic = $result->fetchAssoc()) {
            $retval[] = new Topic($topic['topic']);
        }

        return $retval;
    }

    public function getOpenGraph()
    {
        global $cameralife;
        $retval = array();
        $retval['og:title'] = $this->name;
        $retval['og:type'] = 'website';
        $retval['og:url'] = $cameralife->baseURL . '/topics/' . rawurlencode($this->name);
        if ($cameralife->getPref('rewrite') == 'no') {
            $retval['og:url'] = $cameralife->baseURL . '/topic.php?name=' . rawurlencode($this->name);
        }
        $retval['og:image'] = $cameralife->iconURL('topic');
        $retval['og:image:type'] = 'image/png';
        //$retval['og:image:width'] =
        //$retval['og:image:height'] =
        return $retval;
    }
}
