<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;

/**
 * Theme name - Bootstrap
 * @author William Entriken<cameralife@phor.net>
 * @access public
 * @copyright 2014 William Entriken
 * @todo make this HTML valid
 */
class BottomNavView extends View
{
    public $ownerEmail;
    public $statsUrl;
    public $analyticsId;
    public $extraJavascript;
    public $mainPageOpenGraph;

    public function render()
    {
        ?>
        <hr>

        <footer>
	        <nav class="nav nav-inline">
                <a class="nav-link" href="mailto:<?= htmlspecialchars($this->ownerEmail) ?>"><i class="fa fa-envelope"></i> Contact site owner</a>
                <a class="nav-link" href="<?= htmlspecialchars($this->statsUrl) ?>"><i class="fa fa-signal"></i> Site stats</a>
                <a class="nav-link" href="http://fulldecent.github.io/cameralife"><i class="fa fa-globe"></i> Built with Camera Life</a>
                <a class="nav-link" href="http://visualping.io/?url=<?= htmlspecialchars($this->mainPageOpenGraph->url) ?>"><i class="fa fa-rss"></i> Get updates</a>
            </nav>
        </footer>
    <?php
    }
}
