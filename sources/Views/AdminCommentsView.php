<?php
namespace CameraLife\Views;
use CameraLife\Models as Models;

/**
 * Simple view for rendering a list of configurable preferences
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class AdminCommentsView extends View
{
    /**
     * checkpointId
     *
     * (default value: 0)
     *
     * @var    int
     * @access public
     */
    public $checkpointId = 0;

    /**
     * checkpointDate
     *
     * (default value: '1970-01-01')
     *
     * @var    string
     * @access public
     */
    public $checkpointDate = '1970-01-01';

    /**
     * showFromMe
     *
     * (default value: true)
     *
     * @var    bool
     * @access public
     */
    public $showFromMe = true;

    /**
     * showFromRegistered
     *
     * (default value: true)
     *
     * @var    bool
     * @access public
     */
    public $showFromRegistered = true;

    /**
     * showFromUnregistered
     *
     * (default value: true)
     *
     * @var    bool
     * @access public
     */
    public $showFromUnregistered = true;

    /**
     * commentRecords
     *
     * (default value: array())
     * Array of:
     *
     * @var    Array[]
     * @access public
     */
    public $commentRecords = array();

    public function render()
    {
        echo "<h2>Site comments review</h2>";
        echo "<p class=\"lead\">";
        if ($this->checkpointId > 0) {
            echo "No checkpoint is set, showing all changes.";
        } else {
            echo "Checkpoint is set to $this->checkpointDate, showing changes since then.";
        }
        echo " <a target=\"_blank\" href=\"https://github.com/fulldecent/cameralife/wiki/Checkpoints\"><i class=\"fa fa-info\"></i> Learn about checkpoints</a>";
        echo "</p>";

        echo "<h3>View settings</h3>";
        echo "<form class=\"form-horizontal\" role=\"form\">";
        echo "<div class=\"form-group\">";
        echo "<label for=\"showBy\" class=\"col-sm-2 control-label\">Show changes by</label>";
        echo "<div class=\"col-sm-10\">";
        echo "<label class=\"checkbox inline\"><input type=\"checkbox\" name=\"fromMe\" ".($this->showFromMe?'checked':'')."><i class=\"fa fa-user\"></i> Me</label>";
        echo "<label class=\"checkbox inline\"><input type=\"checkbox\" name=\"fromRegistered\" ".($this->showFromRegistered?'checked':'')."><i class=\"fa fa-user\"></i> Registered users</label>";
        echo "<label class=\"checkbox inline\"><input type=\"checkbox\" name=\"fromUnregistered\" ".($this->showFromUnregistered?'checked':'')."><i class=\"fa fa-user\"></i> Unregistered users</label>";
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">";
        echo "<label for=\"showBy\" class=\"col-sm-2 control-label\">Show changes since</label>";
        echo "<div class=\"col-sm-10\">";
        echo "<p class=\"form-control-static\">Checkpoint is set to $this->checkpointDate, showing changes since then.</p>";
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">";
        echo "<div class=\"col-sm-offset-2 col-sm-10\">";
        echo "<button type=\"submit\" class=\"btn btn-default\">Update view</button>";
        echo "</div>";
        echo "</div>";

        echo "</form>";


        echo "<h3>Results</h3>";

        foreach ($this->commentRecords as $commentRecord) {
            $object = new Models\Photo($commentRecord['id']);
            $openGraphObject = null;
            if (get_class($object) == 'CameraLife\Models\Photo') {
                ///TODO BREAKING MVC HERE
                //              $openGraphObject = new \CameraLife\Controllers\PhotoController($object->get['id']);
                $openGraphObject = new \CameraLife\Controllers\SearchController('');
                $openGraphObject->image = "/cameralife-DEV/media/{$object->record['id']}.jpg?scale=thumbnail&ver=1237246020";
            }

            echo "<div class=\"row\">";
            echo "<div class=\"col-md-2\"><img src=\"{$openGraphObject->image}\"></div>";
            echo "<h4 class=\"col-md-2\">";
            echo "{$auditTrail->record['record_type']} {$auditTrail->record['record_id']}: {$auditTrail->record['value_field']}</h4>";
            echo "<div class=\"col-md-8\">";
            echo "<table class=\"col-md-8 table\">";

            $trails = $auditTrail->getTrailsBackToCheckpoint($this->checkpointId);
            rsort($trails);
            foreach ($trails as $trail) {
                echo "<tr><td>" . htmlspecialchars($trail->record['value_new']);
                echo "<td>{$trail->record['user_date']}";
                echo "<td>{$trail->record['user_ip']}";
                echo "<td>{$trail->record['user_name']}";
                echo "<td>{$trail->record['id']}";
            }
            echo "<tr><td colspan=4>" . htmlspecialchars(end($trails)->previousValue());

            echo "</table>";
            echo "</div></div>";
        }
    }
}