<?php
namespace CameraLife\Controllers;
use CameraLife\Views as Views;
use CameraLife\Models as Models;

/**
 * The install page for new users
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */

class SetupInstall2Controller extends HtmlController
{
    /**
     * prerequesites
     *
     * @var    array of arrays [description=>TEXT, class=>success/warning/danger]
     * @access private
     */
    private $status;

    /**
     * remedies
     *
     * @var    array of arrays [cPanel=>['remedy1', ...], MAMP=>['remedy1', ...]]
     * @access private
     */
    private $remedies;

    public static function getUrl()
    {

        // todo not necessary with update to controller.php
        return constant('BASE_URL') . '/index.php?page=setupInstall2';
    }

    // cannot use parent because database is not accessible
    public function __construct($id = null)
    {
        $this->siteName = null;
        $this->title = $this->siteName;
        $this->type = 'website';
        $this->image = constant('BASE_URL') . '/assets/main.png';
        $this->imageType = 'image/png';
        $this->url = self::getUrl();
    }

    /**
     * checkPrerequesites function.
     *
     * @access private
     * @return bool
     */
    private function checkPrerequesites()
    {
        $prerequesitesAreMet = true;

        // Check OpenID session
        session_start();
        if (isset($_SESSION['openid_identity'])) {
            $this->status[] = ['description'=>"Logged in as <strong>{$_SESSION['openid_identity']}</strong>", 'class'=>'success'];
        } else {
            $this->status[] = ['description'=>'Not logged into OpenID, please go back', 'class'=>'danger'];
            $prerequesitesAreMet = false;
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $this->status[] = ['description'=>'PHP version is ' . phpversion(), 'class'=>'success'];
        } else {
            $this->status[] = ['description'=>'PHP version is ' . phpversion(), 'class'=>'danger'];
            $this->remedies['cPanel'][] = 'Ask your system administrator to install PHP 5.4 or later.';
            $this->remedies['MAMP'][] = 'Upgrade to the latest MAMP version with PHP 5.4 or later.';
            $this->remedies['Ubuntu'][] = 'Perform a Ubuntu dist upgrade to get the latest version which includes PHP 5.4.';
            $prerequesitesAreMet = false;
        }

        // Check MySQL version
        if (function_exists('mysql_query')) {
            $this->status[] = ['description'=>'MySQL is installed', 'class'=>'success'];
        } else {
            $this->status[] = ['description'=>'MySQL is required but not installed', 'class'=>'danger'];
            $this->remedies['cPanel'][] = 'Contact your host to configure MySQL.';
            $this->remedies['Ubuntu'][] = 'See http://php.net/manual/en/ref.mysql.php for information about MySQL.';
            $prerequesitesAreMet = false;
        }

        // Check GD support
        if (function_exists('gd_info')) {
            $info = gd_info();
            if (isset($info['JPEG Support'])) {
                $this->status[] = ['description'=>'PHP-GD is configured properly', 'class'=>'success'];
            } else {
                $this->status[] = ['description'=>'PHP-GD needs to support JPEG, but it does not', 'class'=>'danger'];
                $this->remedies['cPanel'][] = 'Contact your host to configure PHP-GD for JPEG.';
                $info = print_r($info, true);
                $this->remedies['Ubuntu'][] = "See http://us4.php.net/manual/en/ref.image.php for more information about your GD: $info";
                $prerequesitesAreMet = false;
            }
        } else {
            $this->status[] = ['description'=>'PHP-GD is required but not installed', 'class'=>'danger'];
            $this->remedies['cPanel'][] = 'Contact your host to configure PHP-GD.';
            $this->remedies['Ubuntu'][] = '<pre>sudo apt-get install php5-gd\nsudo /etc/init.d/apache2 restart</pre>';
            $prerequesitesAreMet = false;
        }

        // Check MOD_REWRITE
        if (array_key_exists('HTTP_MOD_REWRITE', $_SERVER)) {
            $this->status[] = ['description'=>'MOD_REWRITE is set up properly', 'class'=>'success'];
        } else {
            $this->status[] = ['description'=>'MOD_REWRITE is not set up properly so you cannot use pretty URLs', 'class'=>'warning'];
            $this->remedies['cPanel'][] = 'Contact your host to configure MOD_REWRITE.';
        }

        // Check HTACCESS
        if (file_exists(constant('BASE_DIR') . '/.htaccess')) {
            $this->status[] = ['description'=>'Your file <code>.htaccess</code> is set up properly', 'class'=>'success'];
        } else {
            $this->status[] = ['description'=>'Your <code>.htaccess</code> was not unpacked, check your ZIP file', 'class'=>'warning'];
        }

        // Check directories
        if (is_writable(constant('BASE_DIR') . '/caches')) {
            $this->status[] = ['description'=>'Directory <code>caches/</code> is writable', 'class'=>'success'];
        } else {
            $this->status[] = ['description'=>'Directory <code>caches/</code> needs to be writable', 'class'=>'danger'];
            $this->remedies['cPanel'][] = 'Make <code>' . realpath(constant('BASE_DIR') . '/caches') . '</code> writable';
            $this->remedies['MAMP'][] = '<pre>sudo chmod 777 "' . realpath(constant('BASE_DIR') . '/caches') . '"</pre>';
            $this->remedies['Ubuntu'][] = '<pre>sudo chmod 777 "' . realpath(constant('BASE_DIR') . '/caches') . '"</pre>';
        }

        // Database remedies
        $this->remedies['cPanel'][] = '<a target="_new" href="/cpanel">Login to cPanel</a>';
        $this->remedies['cPanel'][] = 'Access MySQL Databases';
        $this->remedies['cPanel'][] = 'Create Database <b class="val-name">cameralife</b>, see what your database is actually named, enter on the right here (it may be named like mycpanelname_cameralife)';
        $this->remedies['cPanel'][] = 'Add New User: <b class="val-user">username</b> <b class="val-pass">password</b></li>';
        $this->remedies['cPanel'][] = 'Add User To Database: select your user and database, and tick ALL PRIVILEGES';
        $this->remedies['MAMP'][] = 'Open MAMP Preferences | Ports | Set MySQL to 3306 standard';
        $this->remedies['MAMP'][] = 'Login to phpMyAdmin (<a href="http://localhost/phpMyAdminForPHP5/">link for MAMP on localhost</a>)';
        $this->remedies['MAMP'][] = 'Click SQL on the top and paste in:';
        $this->remedies['MAMP'][] = "<pre>sudo mysql
CREATE DATABASE <b class='val-name'>cameralife</b>;
GRANT ALL ON <b class='val-name'>cameralife</b>.*
TO '<b class='val-user'>user</b>'@'<b class='val-host'>localhost</b>'
IDENTIFIED BY '<b class='val-pass'>pass</b>';
quit</pre>";
        $this->remedies['Ubuntu'][] = "<pre>sudo mysql
CREATE DATABASE <b class='val-name'>cameralife</b>;
GRANT ALL ON <b class='val-name'>cameralife</b>.*
TO '<b class='val-user'>user</b>'@'<b class='val-host'>localhost</b>'
IDENTIFIED BY '<b class='val-pass'>pass</b>';
quit</pre>";

        // Config file remedies
        $configFileHtml = <<<'EOF'
&lt;?php
namespace CameraLife\Models;

Database::$dsn = 'mysql:host=<b class="val-host">localhost</b>;dbname=<b class="val-name">cameralife</b>';
Database::$username = '<b class="val-user">user</b>';
Database::$password = '<b class="val-pass">password</b>';
Database::$prefix = '<b class="val-prefix"></b>';
Database::$installedSchemaVersion = 5;
EOF;
        $this->remedies['cPanel'][] = 'Open CPanel and Filemanager and create a new file <code>config.php</code> in the project folder and add these contents to the file:';
        $this->remedies['cPanel'][] = "<pre>$configFileHtml</pre>";
        $this->remedies['MAMP'][] = '<pre>cat &gt; ' . realpath(constant('BASE_DIR')) .'/config.php &lt;&lt;\'EOL\'</pre>';
        $this->remedies['MAMP'][] = "<pre>$configFileHtml</pre>";
        $this->remedies['MAMP'][] = "<pre>EOL</pre>";
        $this->remedies['Ubuntu'][] = '<pre>cat &gt; ' . realpath(constant('BASE_DIR')) .'/config.php &lt;&lt;\'EOL\'</pre>';
        $this->remedies['Ubuntu'][] = "<pre>$configFileHtml</pre>";
        $this->remedies['Ubuntu'][] = "<pre>EOL</pre>";

        return $prerequesitesAreMet;
    }


    public function handleGet($get, $post, $files, $cookies)
    {
        if (file_exists('../../config.php')) {
            throw new \Exception("Camera Life already appears to be set up, because modules/config.inc exists.");
        }

        $prerequesitesAreMet = $this->checkPrerequesites();
        ksort($this->remedies);
?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <title>Install Camera Life</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="//netdna.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
            <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
            <script src="//cdn.jsdelivr.net/jquery/2.1.3/jquery.min.js"></script>
            <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
            <script type="text/javascript">
                var _gaq = _gaq || [];
                _gaq.push(['_setAccount', 'UA-52764-13']);
                _gaq.push(['_trackPageview']);

                (function () {
                    var ga = document.createElement('script');
                    ga.type = 'text/javascript';
                    ga.async = true;
                    ga.src = 'https://ssl.google-analytics.com/ga.js';
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(ga, s);
                })();
            </script>
        </head>

        <body>
            <div class="jumbotron">
                <div class="container">
                    <h2>
                        <i class="fa fa-camera-retro"></i>
                        You are installing Camera Life version <?= constant('CAMERALIFE_VERSION') ?>
                    </h2>
                    <p>To upgrade instead, copy in your old <var>config.php</var> file</p>
                    <p>
                        <a target="_blank" href="http://fulldecent.github.io/cameralife">
                            <i class="glyphicon glyphicon-home"></i>
                            Camera Life project page</a>
                        <a target="_blank" href="mailto:cameralifesupport@phor.net">
                            <i class="glyphicon glyphicon-envelope"></i>
                            Email support</a>
                    </p>

                </div>
            </div>

            <div class="container">
                <?php
                    $icons = ['success'=>'check-circle', 'warning'=>'info-circle', 'danger'=>'times-circle'];
                foreach ($this->status as $status) {
                    $iconHtml = "<i class='fa fa-{$icons[$status['class']]}'></i>";
                    echo "<p class=\"lead text-{$status['class']}\">$iconHtml {$status['description']}</p>\n";
                }
                ?>
                <form method="post">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Setup</h3>
                    </div>
                    <div class="panel-body row">
                        <div class="col-md-6">
                            <div role="tabpanel">
                              <!-- Nav tabs -->
                              <ul class="nav nav-tabs">
                                <?php
                                    $x = 0;
                                foreach ($this->remedies as $system=>$systemRemedies) {
                                    echo '<li class="'.($x++?'':'active').'"><a href="#'.$system.'" data-toggle="tab">'.$system.'</a></li>';
                                }
                                ?>
                              </ul>

                              <!-- Tab panes -->
                              <div class="tab-content">
                                <?php
                                    $x = 0;
                                foreach ($this->remedies as $system=>$systemRemedies) {
                                    echo '<div class="tab-pane '.($x++?'':'active').'" id="'.$system.'">';
                                    foreach ($systemRemedies as $remedy) {
                                        echo "<p>$remedy</p>";
                                    }
                                    echo '</div>';
                                }
                                ?>
                              </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <label class="col-lg-4 control-label" for="host">Database server</label>

                                    <div class="col-lg-8">
                                        <input type="text" id="host" name="host" value="localhost" class="form-control var">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-4 control-label" for="name">Database name</label>

                                    <div class="col-lg-8">
                                        <input type="text" id="name" name="name" value="cameralife" class="form-control var">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-4 control-label" for="user">Database user</label>

                                    <div class="col-lg-8">
                                        <input type="text" id="user" name="user" value="user" class="form-control var">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-4 control-label" for="pass">Database password</label>

                                    <div class="col-lg-8">
                                        <input type="text" id="pass" name="pass" value="pass" class="form-control var">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-4 control-label" for="prefix">Database prefix</label>

                                    <div class="col-lg-8">
                                        <input type="text" id="prefix" name="prefix" placeholder="(optional)" class="form-control var">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-primary">Continue</button>
                    </div>
                </div>
                </form>
            </div>
            <script>
                $('.var').on('change keydown paste input', function(){$('.val-'+this.name).text(this.value)})
            </script>
        </body>
        </html>
<?php
    }

    public function handlePost($get, $post, $files, $cookies)
    {
        session_start();
        if (!isset($post['host'])) {
            throw new \Exception('HOST is missing');
        }
        if (!isset($post['name'])) {
            throw new \Exception('NAME is missing');
        }
        if (!isset($post['user'])) {
            throw new \Exception('USER is missing');
        }
        if (!isset($post['pass'])) {
            throw new \Exception('PASS is missing');
        }
        if (!isset($post['prefix'])) {
            throw new \Exception('PREFIX is missing');
        }                
        if (!isset($_SESSION['openid_identity'])) {
            throw new \Exception('OpenID login is missing');
        }
        Models\Database::$dsn = "mysql:host={$post['host']};dbname={$post['name']}";
        Models\Database::$username = $post['user'];
        Models\Database::$password = $post['pass'];
        Models\Database::$prefix = $post['prefix'];
        Models\Database::setupTables();
        Models\Preferences::setFactoryDefaults();
        
        Models\User::userWithOpenId($_SESSION['openid_identity'], $_SESSION['openid_email']);
        Models\Database::update('users', ['auth'=>5], 'email="'.$_SESSION['openid_email'].'"'); //todo security
        header('Location: ' . MainPageController::getUrl());
        //todo URL / url http://www.teamten.com/lawrence/writings/capitalization_of_initialisms.html        
    }
}
