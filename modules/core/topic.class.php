<?php
namespace CameraLife;

/**
 * Topic class.
 *
 * @author    William Entriken <WillEntriken @gmail.com>
 * @access    public
 * @copyright 2001-2014 William Entriken
 * @extends   Search
 */
class Topic extends Search
{
    /**
     * Returns albums per QUERY, and paging restrictions
     *
     * @access public
     * @return Photo[]
     */
    public function getAlbums()
    {
        //TODO: should not use global CAMERALIFE!    
        global $cameralife;

        switch ($this->sort) {
        case 'newest':
            $sort = 'albums.id desc';
            break;
        case 'oldest':
            $sort = 'albums.id';
            break;
        case 'az':
            $sort = 'description';
            break;
        case 'za':
            $sort = 'description desc';
            break;
        case 'popular':
            $sort = 'albums.hits desc';
            break;
        case 'unpopular':
            $sort = 'albums.hits';
            break;
        case 'rand':
            $sort = 'rand()';
            break;
        default:
            $sort = 'albums.id desc';
        }

        $query = $cameralife->database->Select(
            'albums',
            'id',
            'topic = :topic',
            'ORDER BY ' . $sort . ' ' . 'LIMIT ' . $this->offset . ',' . $this->pageSize,
            null,
            array('topic' => $this->query)
        );

        $albums = array();
        while ($row = $query->fetchAssoc()) {
            $albums[] = new Album($row['id']);
        }

        return $albums;
    }

    /**
     * Counts albums with the topic named QUERY
     *
     * @access public
     * @return int
     */
    public function getAlbumCount()
    {
        //TODO: should not use global CAMERALIFE!    
        global $cameralife;
        return $cameralife->database->SelectOne(
            'albums',
            'COUNT(*)',
            'topic = :topic',
            null,
            null,
            array('topic' => $this->query)
        );
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
        $retval['og:title'] = $this->query;
        $retval['og:type'] = 'website';
        $retval['og:url'] = $cameralife->baseURL . '/topics/' . rawurlencode($this->query);
        if ($cameralife->getPref('rewrite') == 'no') {
            $retval['og:url'] = $cameralife->baseURL . '/topic.php?name=' . rawurlencode($this->query);
        }
        $retval['og:image'] = $cameralife->iconURL('topic');
        $retval['og:image:type'] = 'image/png';
        //$retval['og:image:width'] =
        //$retval['og:image:height'] =
        return $retval;
    }
}
