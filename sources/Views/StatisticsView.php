<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;

/**
 * Shows a welcome page, the "index" for the website
 *
 * @author    William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
 * @access    public
 */
class StatisticsView extends View
{
    /**
     * Statistics for the site
     *
     * @var    Models\Statistics
     * @access public
     */
    public $statistics;

    /**
     * Render the view to standard output
     *
     * @access public
     * @return void
     */
    public function render()
    {
        //$counts = $this->statistics->getCounts();
        $popularPhotos = $this->statistics->getPopularPhotos();
        $popularAlbums = $this->statistics->getPopularTags();
        $funfacts = $this->statistics->getFunFacts();
?>
<h1>Site stats</h1>
<div class="row">
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Most viewed photos</h3>
            </div>
            <div class="panel-body">
                <table class="table table-condensed">
                    <?php
                    foreach ($popularPhotos as $photo) {
                        // fix links
                        $percent = $photo->Get('hits') * 100 / $popularPhotos[0]->Get('hits');
                        echo "<tr><td>";
                        echo "<progress class=\"progress\" value=\"$percent\" max=\"100\" style=\"width:70px\">$percent</progress>";
                        echo "<td>" . number_format($photo->Get('hits'));
                        echo "<td><a href=\"photo.php&#63;id=" . $photo->Get('id') . '">' . $photo->Get(
                            'description'
                        ) . "</a>\n";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Most viewed tags</h3>
            </div>
            <div class="panel-body">
                <table class="table table-condensed">
                    <?php
                    ///TODO: correct links
                    foreach ($popularAlbums as $photo) {
                        $percent = $photo->get('hits') * 100 / $popularPhotos[0]->get('hits');
                        echo "<tr><td>";
                        echo "<progress class=\"progress\" value=\"$percent\" max=\"100\" style=\"width:70px\">$percent</progress>";
                        echo "<td>" . number_format($photo->Get('hits'));
                        echo "<td><a href=\"album.php&#63;id=" . $photo->Get('id') . '">' . $photo->Get(
                            'name'
                        ) . "</a>\n";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Fun facts</h3>
            </div>
            <div class="panel-body">
                <ul>
                    <?php
                    shuffle($funfacts);
                    for ($i = 0; $i < 3; $i++) {
                        echo '<li>' . $funfacts[$i] . "</li>\n";
                    }
                    ?>
                    <li><a href="&#63;">Click here to reload more random facts.</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
    }
}
