<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;
use CameraLife\Controllers as Controllers;

/**
 * Shows a welcome page, the "index" for the website
 *
 * @author    William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
 * @access    public
 */
class AllTagCollectionsView extends View
{
    /**
     * Either: rand, popular, unpopular, newest, newest-folders
     *
     * @var    string
     * @access public
     */
    public $activeSection;

    /**
     * openGraphsForTop
     *
     * @var    OpenGraph[]
     * @access public
     */
    public $openGraphsForTop;

    /**
     * folderAndPhotoOGs
     *
     * @var    mixed
     * @access public
     */
    public $folderAndPhotoOGs;

    /**
     * tag collections
     *
     * @var    TagCollection[]
     * @access public
     */
    public $tagCollections;
    
    public $adminUrl;

    public $rootOpenGraph;

    /**
     * Render the view to standard output
     *
     * @access public
     * @return void
     */
    public function render()
    {
        ?>
        <h2>Tag collections</h2>
        <table class="table">
            <?php

            foreach ($this->tagCollections as $tagCollection) {
                $tCC = new Controllers\TagCollectionController($tagCollection->query);

                echo "<tr><td><h3><a href=\"" . htmlentities($tCC->url) . "\"><i class=\"fa fa-tags\"></i> ";
                echo htmlentities($tCC->title) . "</a></h3>\n";
                $tagCollection->sort = 'rand';
                $tagCollection->SetPage(0, 4);

                echo '<div style=" overflow: hidden; white-space: nowrap; text-overflow: ellipsis; ">';
                $count = 0;
                foreach ($tagCollection->getTags() as $tag) {
                    $tC = new Controllers\TagController($tag->record['id']);
                    if ($count++) {
                        echo ", \n";
                    }
                    echo "<a href=\"" . htmlentities($tC->url) . "\"><i class=\"fa fa-tag\"></i> ";
                    echo htmlentities($tC->title) . "</a>";
                }
                echo ", ...</div>\n";
            }
            //todo fix url
            echo "<tr><td><h3><a href=\"search.php&#63;albumhelp=1&amp;q=\">... create a featured tag</a></h3>";

            ?>
        </table>
        <?php
    }
}
