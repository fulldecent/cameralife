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
    public $reviewsDone;  
    
    /**
     * Number of items which have not yet been reviewed (including the ones shown)
     * 
     * @var mixed
     * @access public
     */
    public $reviewsRemaining;  
    
    public $isUsingHttps;
    
    public $myUrl;
  
    public $lastReviewItem;
  
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
        echo "<h2>Review New photos</h2>";
        
        $percentDone = 0;
        $percentDoing = 0;
        if ($this->reviewsDone + $this->reviewsRemaining) {
            $percentDone = $this->reviewsDone * 100 / ($this->reviewsDone + $this->reviewsRemaining);
            $percentDoing = count($this->photos) * 100 / ($this->reviewsDone + $this->reviewsRemaining);
        }
        echo '<div class="progress">';
        echo '<div class="progress-bar progress-bar-success" style="width: ' . $percentDone . '%;"></div>';
        echo '<div class="progress-bar progress-bar-info" style="width: ' . $percentDoing . '%;"></div>';
        echo '</div>';
        
        if (!$this->isUsingHttps) {
            echo "<p class=\"lead alert alert-danger\"><strong>Warning:</strong> You are viewing this page, which includes private photos, without HTTPS</p>";
        }  
        
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
        
        $action = Controllers\AdminPreferenceChangeController::getUrl();

        echo '<form method="post" action="' . $action. '">';
        echo '<input type="hidden" name="target" value="' . htmlspecialchars($this->myUrl) . '">';
        echo '<input type="hidden" name="CameraLife|checkpointphotos" value="' . htmlspecialchars($this->lastReviewItem) . '">';
        echo '<button class="btn btn-primary btn-lg">Mark these items as reviewed</button>';        
        
        echo '</form>';
    }
}