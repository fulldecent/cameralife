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

    public function render()
    {
        echo <<<EOL
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-52764-13']);
        _gaq.push(['_trackPageview']);

        (function () {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();
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
        echo "<div class=\"panel panel-$class\">";
        echo "<div class=\"panel-heading\"><h3 class=\"panel-title\">Logs</h3></div>";
        echo "<div class=\"panel-body\">";
        if ($this->numNewLogs) {
            echo "<p>There are $this->numNewLogs logged actions since the last checkpoint</p>";
        } else {
            echo "<p>No changes have been made since the last checkpoint</p>";
        }
        echo "<p><a class=\"btn btn-$class\" href=\"$this->logsUrl\"><i class=\"fa fa-backward\"></i> View and
                                rollback site actions</a></p>";
        echo "</div></div></div>";

        // Comments
        $class = $this->numNewComments ? 'warning' : 'default';
        echo "<div class=\"col-sm-4\">";
        echo "<div class=\"panel panel-$class\">";
        echo "<div class=\"panel-heading\"><h3 class=\"panel-title\">Comments</h3></div>";
        echo "<div class=\"panel-body\">";
        if ($this->numNewComments) {
            echo "<p>There are $this->numNewComments logged comments since the last checkpoint</p>";
        } else {
            echo "<p>No comments have been made since the last checkpoint</p>";
        }
        echo "<p><a class=\"btn btn-$class\" href=\"$this->commentsUrl\"><i class=\"fa fa-user\"></i> View and censor site
                                comments</a></p>";
        echo "</div></div></div>";

        // Files
        $class = $this->numFlagged ? 'danger' : 'default';
        echo "<div class=\"col-sm-4\">";
        echo "<div class=\"panel panel-$class\">";
        echo "<div class=\"panel-heading\"><h3 class=\"panel-title\">Flagged and private photos</h3></div>";
        echo "<div class=\"panel-body\">";
        if ($this->numFlagged) {
            echo "<p>$this->numFlagged photos have been flagged by visitors</p>";
        } else {
            echo "<p>No photos have been flagged by visitors</p>";
        }
        echo "<p><a class=\"btn btn-$class\" href=\"$this->photosUrl\"><i class=\"fa fa-folder-open\"></i> Review photos</a></p>";
        echo "<p><a class=\"btn btn-default\" href=\"$this->thumbnailUrl\"><i class=\"fa fa-folder-open\"></i> Update thumbnails</a></p>";
        echo "</div></div></div>";


        echo "</div>";
        echo "<h1>Configuration</h1>";
        echo "<div class=\"row\">";

        // Users
        $class = $this->numNewUsers ? 'warning' : 'default';
        echo "<div class=\"col-sm-3\">";
        echo "<div class=\"panel panel-$class\">";
        echo "<div class=\"panel-heading\"><h3 class=\"panel-title\">Users and security</h3></div>";
        echo "<div class=\"panel-body\">";
        if ($this->numNewUsers) {
            echo "<p>$this->numNewUsers new users have registered</p>";
        } else {
            echo "<p>No new unverified users</p>";
        }
        echo "<p><a class=\"btn btn-$class\" href=\"$this->securityUrl\"><i class=\"fa fa-lock\"></i> Manage users &amp; security</a></p>";
        echo "</div></div></div>";


        // Appearance
        echo "<div class=\"col-sm-3\">";
        echo "<div class=\"panel panel-default\">";
        echo "<div class=\"panel-heading\"><h3 class=\"panel-title\">Appearance</h3></div>";
        echo "<div class=\"panel-body\">";
        echo "<p><a class=\"btn btn-default\" href=\"$this->appearanceUrl\"><i class=\"fa fa-star\"></i> Set theme preferences</a></p>";
        echo "</div></div></div>";

        // FileStore
        echo "<div class=\"col-sm-3\">";
        echo "<div class=\"panel panel-default\">";
        echo "<div class=\"panel-heading\"><h3 class=\"panel-title\">File storage</h3></div>";
        echo "<div class=\"panel-body\">";
        echo "<p>Photos can be stored on your web server, a remote server, Amazon S3, etc.</p>";
        echo "<p><a class=\"btn btn-default\" href=\"$this->fileStoreUrl\"><i class=\"fa fa-folder-open\"></i> Configure FileStore</a></p>";
        echo "</div></div></div>";

        // Feedback
        echo "<div class=\"col-sm-3\">";
        echo "<div class=\"panel panel-default\">";
        echo "<div class=\"panel-heading\"><h3 class=\"panel-title\">Feedback</h3></div>";
        echo "<div class=\"panel-body\">";
        echo "<p>How do you like Camera Life? Let us know.</p>";
        echo "<p>";
        echo "<a class=\"btn btn-default\" href=\"https://github.com/fulldecent/cameralife/issues\"><i class=\"fa fa-flag\"></i> Report an issue</a>";
        echo "<a class=\"btn btn-default\" href=\"https://github.com/fulldecent/cameralife\"><i class=\"fa fa-github\"></i> Get project updates</a>";
        echo "</p>";
        echo "</div></div></div>";


        echo "</div>";

    }
}
