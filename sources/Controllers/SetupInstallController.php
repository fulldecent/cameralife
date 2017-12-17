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

class SetupInstallController extends HtmlController
{
    public $model;

    public static function getUrl()
    {
        return constant('BASE_URL') . '/index.php?page=setupInstall';
    }

    function uuidv4()
    {
        return implode('-', [
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(chr((ord(random_bytes(1)) & 0x0F) | 0x40)) . bin2hex(random_bytes(1)),
            bin2hex(chr((ord(random_bytes(1)) & 0x3F) | 0x80)) . bin2hex(random_bytes(1)),
            bin2hex(random_bytes(6))
        ]);
    }

    // cannot use parent because database is not accessible
    public function __construct($modelId = null)
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
        global $_SERVER;
        $prerequesitesAreMet = true;

        // Check PHP version
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $this->status[] = ['description'=>'PHP version is ' . phpversion(), 'class'=>'success'];
        } else {
            $this->status[] = ['description'=>'PHP version is ' . phpversion(), 'class'=>'danger'];
            $this->remedies['cPanel'][] = 'Ask your system administrator to install PHP 5.4 or later.';
            $this->remedies['MAMP'][] = 'Upgrade to the latest MAMP version with PHP 5.4 or later.';
            $this->remedies['Ubuntu'][] = 'Perform a Ubuntu dist upgrade to get the latest version which includes PHP 5.4.';
            $prerequesitesAreMet = false;
        }

        // Check SQLite version
        if (in_array('sqlite', \PDO::getAvailableDrivers(), TRUE)) {
            $this->status[] = ['description'=>'SQLite PDO is installed', 'class'=>'success'];
        } else {
            $this->status[] = ['description'=>'SQLite PDO is required but not installed', 'class'=>'danger'];
            $this->remedies['cPanel'][] = 'Contact your host to configure MySQL.';
            $this->remedies['Ubuntu'][] = 'See http://php.net/manual/en/ref.sqlite.php for information about SQLite.';
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
        if (is_writable(constant('BASE_DIR') . '/config/caches')) {
            $this->status[] = ['description'=>'Directory <code>config/caches/</code> is writable', 'class'=>'success'];
        } else {
            $this->status[] = ['description'=>'Directory <code>config/caches/</code> needs to be writable', 'class'=>'danger'];
            $this->remedies['cPanel'][] = 'Make <code>' . realpath(constant('BASE_DIR') . '/config/caches') . '</code> writable';
            $this->remedies['MAMP'][] = '<pre>sudo chmod 777 "' . realpath(constant('BASE_DIR') . '/config/caches') . '"</pre>';
            $this->remedies['Ubuntu'][] = '<pre>sudo chmod 777 "' . realpath(constant('BASE_DIR') . '/config/caches') . '"</pre>';
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
Database::$schemaVersion = 5;
EOF;
        $this->remedies['cPanel'][] = 'Open CPanel and Filemanager and create a new file <code>config/config.php</code> in the project folder and add these contents to the file:';
        $this->remedies['cPanel'][] = "<pre>$configFileHtml</pre>";
        $this->remedies['MAMP'][] = '<pre>cat &gt; ' . realpath(constant('BASE_DIR')) .'/config/config.php &lt;&lt;\'EOL\'</pre>';
        $this->remedies['MAMP'][] = "<pre>$configFileHtml</pre>";
        $this->remedies['MAMP'][] = "<pre>EOL</pre>";
        $this->remedies['Ubuntu'][] = '<pre>cat &gt; ' . realpath(constant('BASE_DIR')) .'/config/config.php &lt;&lt;\'EOL\'</pre>';
        $this->remedies['Ubuntu'][] = "<pre>$configFileHtml</pre>";
        $this->remedies['Ubuntu'][] = "<pre>EOL</pre>";

        return $prerequesitesAreMet;
    }

    public function handleGet($get, $post, $files, $cookies)
    {
        if (file_exists('../../config.php')) {
            throw new \Exception("Camera Life already appears to be set up, because modules/config.inc exists.");
        }

        $canInstall = $this->checkPrerequesites();
        ksort($this->remedies);
        ?>
        <!doctype html>
        <html lang="en">
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
          <title>Install Camera Life</title>
          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
          <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
          <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
          <!-- CAMERALIFE PHONE HOME Global site tag (gtag.js) - Google Analytics -->
          <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('event', 'sign_up', {'checkout_step': 1});
            gtag('config', 'UA-52764-13');
          </script>
        </head>

        <body>
          <div class="jumbotron lead py-5">
            <div class="container">
              <h1>
                <i class="fa fa-camera-retro"></i>
                Installing Camera Life version <?= constant('CAMERALIFE_VERSION') ?>
              </h1>
              <p>To upgrade instead, copy in your old <var>config/config.php</var> file.</p>
              <p>
                <a class="btn btn-outline-secondary" target="_blank" href="http://fulldecent.github.io/cameralife">
                  <i class="fa fa-home"></i>
                  Camera Life project page
                </a>
                <a class="btn btn-outline-secondary" target="_blank" href="mailto:cameralifesupport@phor.net">
                  <i class="fa fa-envelope"></i>
                  Email support
                </a>
              </p>
            </div>
          </div>
          <div class="container">
            <?php
            $icons = ['success'=>'check-circle', 'warning'=>'info-circle', 'danger'=>'times-circle'];
            foreach ($this->status as $status) {
                $iconHtml = "<i class='fa fa-{$icons[$status['class']]}'></i>";
                echo "<p class=\"lead text-{$status['class']}\">$iconHtml {$status['description']}</p>\n";
                echo "<script>gtag('send', 'event', 'install', 'prerequisite', '{$status['description']}');</script>";
            }
            if (!$canInstall) exit();
            $password = $this->uuidv4();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            ?>

            <hr class="my-5">

            <p class="lead"><strong>Your admin access code is <code><?= $password ?></code></strong> so please keep that somewhere.</p>

            <p class="lead">To complete installation, copy the <code>config/config-example.php</code> file to <code>config/config.php</code>. Then edit the line <code>$adminAccessCodeHash</code> from the original value of <code>''</code> to become <code>'<?= $hash ?>'</code></p>

            <p>After that, you're done so <a class="btn btn-primary" href="?">Continue to My Site</a>.
          </div>
        </body>
        </html>

<?php
    }
}
