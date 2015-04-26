<?php
namespace CameraLife\Views;
use CameraLife\Models as Models;
use CameraLife\Controllers as Controllers;

/**
 * Simple view for rendering a list of configurable preferences
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class AdminPhotosView extends View
{
    /**
     * Number of items which have not yet been reviewed (including the ones shown)
     * 
     * @var mixed
     * @access public
     */
    public $reviewsRemaining;  
  
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
     * showChangedPhotos
     *
     * (default value: true)
     *
     * @var    bool
     * @access public
     */
    public $showChangedPhotos = true;

    /**
     * showChangedTags
     *
     * (default value: true)
     *
     * @var    bool
     * @access public
     */
    public $showChangedTags = true;

    /**
     * showChangedUsers
     *
     * (default value: true)
     *
     * @var    bool
     * @access public
     */
    public $showChangedUsers = true;

    /**
     * showChangedPrefs
     *
     * (default value: true)
     *
     * @var    bool
     * @access public
     */
    public $showChangedPrefs = true;

    /**
     * auditTrails
     *
     * (default value: array())
     *
     * @var    Models\AuditTrail[]
     * @access public
     */
    public $auditTrails = array();
    
    /**
     * The list of photos to show
     * 
     * (default value: array())
     * 
     * @var array
     * @access public
     */
    public $photos = array();
  

    public function render()
    {
        echo "<h2>New photos</h2>";
        if (count($this->photos) < $this->reviewsRemaining) {
            echo "<p class=\"lead\">There are " . number_format($this->reviewsRemaining) . " new photos since your last review, the first " . number_format(count($this->photos)) . " are shown below.</p>";
        } else {
            echo "<p class=\"lead\">There are " . number_format($this->reviewsRemaining) . " new photos since your last review.</p>";
          
        }

        echo "<div class=\"row\">";
        
        $height = Models\Preferences::valueForModuleWithKey('CameraLife', 'thumbsize');
        foreach ($this->photos as $photo) {
            $url = Controllers\PhotoController::getUrlForID($photo->id);
          
            $color = $photo->get('status') == 0 ? 'default' : 'danger';
            echo '<div class="col-md-2 col-sm-4 bg-'.$color.'" style="height:' . $height . 'px">';
            
            echo '<a href="' . htmlspecialchars($url) . '">';
            echo '<img class="img-responsive center-block img-rounded" width="' . intval($photo->get('tn_width')) . '" src="' . htmlspecialchars(
                $photo->getMediaURL('thumbnail')
            ) . '" alt="' . htmlentities($photo->get('description')) . '" />';
            echo '</a>';
            echo '</div>';
        }
        
        echo '</div>';
    }
}