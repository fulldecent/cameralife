<?php
namespace CameraLife\Models;

/**
 * Topic class.
 *
 * @author    William Entriken <WillEntriken @gmail.com>
 * @access    public
 * @copyright 2001-2014 William Entriken
 * @extends   Search
 */
class TagCollection extends Search
{
    public function __construct($query)
    {
        parent::__construct($query);
    }

    /**
     * Returns tags per QUERY, and paging restrictions
     *
     * @access public
     * @return Photo[]
     */
    public function getTags()
    {
        $sort = $this->tagSortSqlForOption($this->sort);
        $query = Database::select(
            'albums',
            'id',
            'topic = :topic',
            'ORDER BY ' . $sort . ' ' . 'LIMIT ' . $this->offset . ',' . $this->pageSize,
            null,
            array('topic' => $this->query)
        );

        $albums = array();
        while ($row = $query->fetchAssoc()) {
            $albums[] = new Tag($row['id']);
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
        return Database::selectOne(
            'albums',
            'COUNT(*)',
            'topic = :topic',
            null,
            null,
            array('topic' => $this->query)
        );
    }


    public static function getCollections()
    {
        global $cameralife;
        $retval = array();
        $result = Database::select('albums', 'DISTINCT topic');
        while ($row = $result->fetchAssoc()) {
            $retval[] = new TagCollection($row['topic']);
        }
        return $retval;
    }
}
