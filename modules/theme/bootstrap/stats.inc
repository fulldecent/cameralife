<?php
/**
 * Displays popular photos and albums,and fun facts such as the amount of time a site has been online
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */

# $cameralife and $album are set by showPage()
$cameralife->getFeature('fileStore');

$stats = new Stats;
$counts = $stats->getCounts();
$popularPhotos = $stats->getPopularPhotos();
$popular_albums = $stats->getPopularAlbums();
$funfacts = $stats->getFunFacts();

//TODO THIS IS NOT ACCURATE
$cameralife->theme->Header($cameralife);
?>
<h1>Site stats</h1>
<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Most popular photos</h3>
            </div>
            <div class="panel-body">
                <table class="table table-condensed">
                    <?php
                    foreach ($popularPhotos as $photo) {
                        $percent = $photo->Get('hits') * 100 / $popularPhotos[0]->Get('hits');
                        echo "<tr><td>";
                        echo "<div style=\"width:50px; clear:left\" class=\"progress\"><div class=\"progress-bar progress-info\" style=\"width: $percent%\"></div></div>";
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
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Most popular albums</h3>
            </div>
            <div class="panel-body">
                <table class="table table-condensed">
                    <?php
                    foreach ($popular_albums as $photo) {
                        $percent = $photo->Get('hits') * 100 / $popularPhotos[0]->Get('hits');
                        echo "<tr><td>";
                        echo "<div style=\"width:50px; clear:left\" class=\"progress\"><div class=\"progress-bar progress-bar-success\" style=\"width: $percent%\"></div></div>";
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
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Fun facts</h3>
            </div>
            <div class="panel-body">
                <ul>
                    <li><?= $cameralife->getPref('sitename') ?> has been online since <?=
                        $cameralife->getPref(
                            'sitedate'
                        ) ?> (<b><?= $counts['daysonline'] ?> days</b>).
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
$cameralife->theme->Footer();
?>
