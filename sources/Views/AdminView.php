<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;

/**
 * Simple view for rendering a list of configurable preferences
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class AdminView extends View
{
    /**
     * The version this system is running
     *
     * @var    mixed
     * @access public
     */
    public $runningVersion;

    /**
     * The latest released version
     *
     * @var    mixed
     * @access public
     */
    public $latestVersion;

    /**
     * Number of logged actions since last checkpoint
     *
     * @var    mixed
     * @access public
     */
    public $numNewLogs;

    /**
     * Number of logged comments since last checkpoint
     *
     * @var    mixed
     * @access public
     */
    public $numNewComments;

    /**
     * Number of new users that have not been approved
     *
     * @var    mixed
     * @access public
     */
    public $numNewUsers;

    /**
     * Number of flagged photos
     *
     * @var    mixed
     * @access public
     */
    public $numFlagged;

    /**
     * logsUrl
     *
     * @var    mixed
     * @access public
     */
    public $logsUrl;

    /**
     * commentsUrl
     *
     * @var    mixed
     * @access public
     */
    public $commentsUrl;

    /**
     * photosUrl
     *
     * @var    mixed
     * @access public
     */
    public $photosUrl;

    /**
     * securityUrl
     *
     * @var    mixed
     * @access public
     */
    public $securityUrl;

    /**
     * appearanceUrl
     *
     * @var    mixed
     * @access public
     */
    public $appearanceUrl;

    /**
     * fileStoreUrl
     *
     * @var    mixed
     * @access public
     */
    public $fileStoreUrl;

    /**
     * thumbnailUrl
     *
     * @var    mixed
     * @access public
     */
    public $thumbnailUrl;

    /**
     * rescanUrl
     *
     * @var mixed
     * @access public
     */
    public $rescanUrl;

    public function render()
    {
        echo <<<EOL
        <!-- CAMERALIFE PHONE HOME Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-52764-13"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('event', 'login', {'affiliation': {$this->runningVersion}});
          gtag('config', 'UA-52764-13');
        </script>
EOL;

        if ($this->latestVersion == $this->runningVersion) {
            echo "<p class=\"alert alert-success\">You are running Camera Life {$this->runningVersion}, the latest version</p>\n";
        } else {
            echo "<p class=\"alert alert-danger\">You are running Camera Life {$this->runningVersion}, a newer version, {$this->latestVersion}, is available. Please run <code>git pull</code> or visit the <a href=\"http://fulldecent.github.io/cameralife/\">Camera Life homepage</a>.</p>\n";
        }

        echo "<h1>Administration</h1>";
        echo "<div class=\"row\">";

        // Logs
        $class = $this->numNewLogs ? 'warning' : 'default';
        echo "<div class=\"col-sm-4\">";
        echo "<div class=\"card card-block card-$class\">";
        echo "<h3 class=\"card-title\">Logs</h3>";
        if ($this->numNewLogs) {
            echo "<p class=\"card-text\">There are $this->numNewLogs logged actions since the last checkpoint</p>";
        } else {
            echo "<p class=\"card-text\">No changes have been made since the last checkpoint</p>";
        }
        echo "<a class=\"card-link\" href=\"$this->logsUrl\"><i class=\"fa fa-backward\"></i> View and
                                rollback site actions</a>";
        echo "</div></div>";

        // Files
        $class = $this->numFlagged ? 'danger' : 'default';
        echo "<div class=\"col-sm-4\">";
        echo "<div class=\"card card-block card-$class\">";
        echo "<h3 class=\"card-title\">Flagged and private photos</h3>";
        if ($this->numFlagged) {
            echo "<p class=\"card-text\">$this->numFlagged photos have been flagged by visitors</p>";
        } else {
            echo "<p class=\"card-text\">No photos have been flagged by visitors</p>";
        }
        echo "<a class=\"card-link\" href=\"$this->photosUrl\"><i class=\"fa fa-folder-open\"></i> Review photos</a>";
        echo "<a class=\"card-link\" href=\"$this->thumbnailUrl\"><i class=\"fa fa-folder-open\"></i> Update thumbnails</a>";
        echo "<a class=\"card-link\" href=\"$this->rescanUrl\"><i class=\"fa fa-folder-open\"></i> Rescan for photos</a>";
        echo "</div></div>";

        // Feedback
        echo "<div class=\"col-sm-4\">";
        echo "<div class=\"card card-block\">";
        echo "<h3 class=\"card-title\">Feedback</h3>";
        echo "<p>How do you like Camera Life? Let us know.</p>";
        echo "<a class=\"card-link\" href=\"https://github.com/fulldecent/cameralife/issues\"><i class=\"fa fa-flag\"></i> Report an issue</a>";
        echo "<a class=\"card-link\" href=\"https://github.com/fulldecent/cameralife\"><i class=\"fa fa-github\"></i> Get project updates</a>";
        echo "</div></div>";


        echo "</div>";
        echo "<h1>Configuration</h1>";
        echo "<div class=\"row\">";

        // Users
        $class = $this->numNewUsers ? 'warning' : 'default';
        echo "<div class=\"col-sm-4\">";
        echo "<div class=\"card card-block card-$class\">";
        echo "<h3 class=\"card-title\">Users and security</h3>";
        if ($this->numNewUsers) {
            echo "<p class=\"card-text\">$this->numNewUsers new users have registered</p>";
        } else {
            echo "<p class=\"card-text\">No new unverified users</p>";
        }
        echo "<a class=\"card-link\" href=\"$this->securityUrl\"><i class=\"fa fa-lock\"></i> Manage users &amp; security</a>";
        echo "</div></div>";

        // Appearance
        echo "<div class=\"col-sm-4\">";
        echo "<div class=\"card card-block\">";
        echo "<h3 class=\"card-title\">Appearance</h3>";
        echo "<a class=\"card-link\" href=\"$this->appearanceUrl\"><i class=\"fa fa-star\"></i> Set theme preferences</a>";
        echo "</div></div>";

        // FileStore
        echo "<div class=\"col-sm-4\">";
        echo "<div class=\"card card-block\">";
        echo "<h3 class=\"card-title\">File storage</h3>";
        echo "<div class=\"panel-body\">";
        echo "<p class=\"card-text\">Photos can be stored on your web server, a remote server, Amazon S3, etc.</p>";
        echo "<a class=\"card-link\" href=\"$this->fileStoreUrl\"><i class=\"fa fa-folder-open\"></i> Configure FileStore</a></p>";
        echo "</div></div></div>";

        echo "</div>";

    }
}
