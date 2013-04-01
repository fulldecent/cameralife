<?php
/**
 * Displays post installation notifcation messages
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

$continue = true;
if (file_exists('../modules/config.inc')) {
  die("Camera Life already appears to be set up, because modules/config.inc exists.");
}

###### CHECK ALL PREREQUESITES #####

$checkPrerequesites = array(); // each is {desc:HTML,type:warning/error/info/success}
$fixes = array(); // each is {ubuntu:HTML,cpanel:HTML}

if (function_exists('mysql_query')) {
  $checkPrerequesites[] = array('desc'=>'MySQL is installed', 'type'=>'success');
} else {
  $checkPrerequesites[] = array('desc'=>'MySQL is required, but not installed', 'type'=>'error');
  $fixes[] = array('ubuntu'=>'See http://php.net/manual/en/ref.mysql.php for information about MySQL',
                   'cpanel'=>'Contact your host to configure MySQL');
}

if (function_exists('gd_info')) {
  $info = @gd_info();
  if ($info['JPG Support'] || $info['JPEG Support']) {
    $checkPrerequesites[] = array('desc'=>'GD installed and configured properly', 'type'=>'success');
  } else {
    $checkPrerequesites[] = array('desc'=>'GD needs to support JPEG, but it does not', 'type'=>'error');
    $fixes[] = array('ubuntu'=>"See http://us4.php.net/manual/en/ref.image.php for more information. Following is configuration about your GD: ".print_r($info,true),
                     'cpanel'=>'Contact your host to configure PHP-GD for JPEG');
  }
} else {
  $checkPrerequesites[] = array('desc'=>'GD is required but not installed', 'type'=>'error');
  $fixes[] = array('ubuntu'=>'<pre>sudo apt-get install php5-gd\nsudo /etc/init.d/apache2 restart</pre>',
                   'cpanel'=>'Contact your host to configure PHP-GD');
}

if (get_magic_quotes_gpc()) {
  $checkPrerequesites[] = array('desc'=>'Magic quotes is disabled, as it should be', 'type'=>'success');
} else {
  $checkPrerequesites[] = array('desc'=>'Magic quotes is enabled, you want to turn this off', 'type'=>'warning');
  $fixes[] = array('ubuntu'=>"Disable magic quotes, see http://php.net/manual/en/security.magicquotes.php",
                   'cpanel'=>'Contact your host to disable magic quotes');
}

$url = 'http://' . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.$_SERVER['SERVER_PORT']). str_replace('index.php','',$_SERVER['PHP_SELF']) . 'images/blank.gif';
$fh = fopen($url, 'r');
while ($fh && !feof($fh)) {
  $data .= fread($fh, 8192);
}
if (md5($data) == 'accba0b69f352b4c9440f05891b015c5')
  $checkPrerequesites[] = array('desc'=>'Content negotiation is configured correctly', 'type'=>'success');
else {
  $checkPrerequesites[] = array('desc'=>'Content negotiation is not configured correctly', 'type'=>'error');
  $fixes[] = array('ubuntu'=>"Your server doesn't appear to support
                CONTENT NEGOTIATION, or is not allowing HTACCESS OVERRIDES. Or maybe the file .htaccess
                was not copied. You're going to need to fix that before you continue.",
                   'cpanel'=>"Your server doesn't appear to support
                CONTENT NEGOTIATION, or is not allowing HTACCESS OVERRIDES. Or maybe the file .htaccess
                was not copied. You're going to need to fix that before you continue.");
}

$url = 'http://' . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.$_SERVER['SERVER_PORT']). str_replace('index.php','',$_SERVER['PHP_SELF']) . 'images/clear.gif';
if(@fopen($url, 'r'))
  $checkPrerequesites[] = array('desc'=>'Mod rewrite is set up correctly, you can use pretty URLs', 'type'=>'success');
else {
  $checkPrerequesites[] = array('desc'=>'MOD REWRITE is not set up, enable this to use pretty URLs.', 'type'=>'warning');
  $fixes[] = array('ubuntu'=>"Set up MOD REWRITE to get pretty URLs, see http://stackoverflow.com/q/869092",
                   'cpanel'=>'Contact your host to set up MOD REWRITE for pretty URLs');
}

$writable = array();
$writable[] = array('modules','you will need to manually paste a file in there later','warning');
$writable[] = array('images/photos','you will not be able to upload photos from inside Camera Life','error');
$writable[] = array('images/cache','but needs to be','error');
$writable[] = array('images/deleted','but needs to be','error');
$allerrors = 0;
$unwritable = array();
foreach ($writable as $a) {
  $fullDir = dirname(dirname(__FILE__)).'/'.$a[0];
  if (is_writable($fullDir)) {
    //$checkPrerequesites[] = array('desc'=>"Directory <code>$fullDir</code> is writable", 'type'=>'success');
  } else {
    $checkPrerequesites[] = array('desc'=>"Directory <code>$fullDir</code> is not writable {$a[1]}", 'type'=>$a[2]);
    $fixes[] = array('ubuntu'=>"<pre>sudo chmod 777 \"$fullDir\"</pre>");
  }
}

if (file_exists('../.htaccess') && !is_writable('../.htaccess')) {
  $checkPrerequesites[] = array('desc'=>'Your file <code>.htaccess</code> exists but is not writable. Please fix this now, or you will have to edit the file manually later', 'type'=>'warning');
  $fullDir = dirname(dirname(__FILE__)).'/.htaccess';
  $fixes[] = array('ubuntu'=>"<pre>sudo chmod 777 \"$fullDir\"</pre>");
} elseif (!copy ('example.htaccess', '../.htaccess')) {
  $checkPrerequesites[] = array('desc'=>'We cannot automatically edit .htaccess. Please fix this now, or you will have to edit the file manually later.', 'type'=>'warning');
  $fullDir = dirname(dirname(__FILE__)).'/.htaccess';
  $fixes[] = array('ubuntu'=>"<pre>sudo cp ".dirname(__FILE__)."/example.htaccess ".dirname(dirname(__FILE__))."/.htaccess\nsudo chmod 777  ".dirname(dirname(__FILE__))."/.htaccess</pre>");
} else {
  $checkPrerequesites[] = array('desc'=>'Your <code>.htaccess</code> is set up properly', 'type'=>'success');
}

if (file_exists('.htaccess'))
  $checkPrerequesites[] = array('desc'=>'Your <code>.htaccess</code> unpacked properly', 'type'=>'success');
else {
  $checkPrerequesites[] = array('desc'=>'Your <code>.htaccess</code> did not unpack from the zipball/tarball properly', 'type'=>'error');
  $fixes[] = array('ubuntu'=>"Download the .htaccess file from the same place you got Camera Life",
                   'cpanel'=>'Download the .htaccess file from the same place you got Camera Life');
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Install Camera Life</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le styles -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <style type="text/css">
    </style>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-52764-13']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
    </script>
  </head>

  <body>

    <div class="navbar">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand">INSTALL CAMERA LIFE</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active"><a>1. Prerequisites</a></li>
              <li><a>2. Database</a></li>
              <li><a>3. Use Camera Life</a></li>
            </ul>
            <a class="btn pull-right" href="mailto:cameralifesupport@phor.net">
              <i class="icon-envelope"></i>
              Email support
            </a>
            <a class="btn pull-right" href="http://fulldecent.github.com/cameralife">
              <i class="icon-home"></i>
              Camera Life homepage
            </a>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">

<!--      <p style="text-align:center"><img src="images/intro1.png"><p> -->

      <div class="well">
        <h2>You are installing Camera Life <?php readfile('../VERSION') ?></h2>
        <p>Thank you for choosing to install Camera Life. We hope you will find this software is easy to use and fun. This project is licensed under the GNU General Public License, version 2. If you are upgrading from a previous version of Camera Life, stop and read the file <a href="../UPGRADE">UPGADE</a>.

      </div>

      <div class="row">
        <div class="span6">
          <h2>Checking prerequisites</h2>
<?php
$icons = array('warning'=>'icon-question-sign','error'=>'icon-remove-sign','info'=>'icon-info-sign','success'=>'icon-ok-sign');
foreach ($checkPrerequesites as $prequesiteResult) {
  $icon = $icons[$prequesiteResult['type']];
  echo "<p class=\"text-{$prequesiteResult[type]}\"><i class=\"$icon\"></i> {$prequesiteResult[desc]}</p>\n";
}

?>
          <p><a class="btn" href="?"><i class="icon-refresh"></i> Check again</a></p>
        </div>
        <div class="span6">
          <h2>Fixes (instructions for Ubuntu)</h2>
<?php
foreach ($fixes as $fix) {
  echo "<p>{$fix[ubuntu]}</p>\n";
}
?>
         </div>
      </div>

<?php
$continue = 1;
foreach ($checkPrerequesites as $result) {
  if ($result['type'] == 'error')
    $continue = 0;
}
if ($continue) {
?>
      <div class="well">
        <h2>Prerequisites OK / Set up database</h2>
      </div>
<?php
} else {
?>
      <div class="well">
        <h2>You must fix prerequisite errors before continuing</h2>
      </div>
    </div>
  </body>
</html>
<?php
  exit(0);
}
?>

      <div class="row">
        <div class="span4">
          <h2>Linux instructions</h2>
            <pre>$ sudo mysql
mysql&lt; CREATE DATABASE <b>cameralife</b>;
mysql&lt; GRANT ALL ON <b>cameralife</b>.* TO <b>user</b>@<b>localhost</b> IDENTIFIED BY '<b>pass</b>';</pre>
        </div>
        <div class="span4">
          <h2>CPanel instructions</h2>
          <ul>
            <li><a target="_new" href="http://phor.net/cpanel">Login to cPanel</a></li>
            <li>Click <a target="_new" href="http://phor.net:2082/frontend/x3/sql/index.html">MySQL Databases</a></li>
            <li>Create Database: enter <b>cameralife</b>, read what your database is actually named, go back</li>
            <li>Add New User: <b>username</b> <b>password</b></li>
            <li>Add User To Database: select your user and database, and tick ALL PRIVILEGES</li>
            <li>Note, your cPanel account name will proceed your database and user names below. For example, your database name will be mycpanelname_cameralife</li>
          </ul>
        </div>
        <div class="span4">
          <h2>phpMyAdmin/MAMP</h2>
          <ul>
            <li>If using MAMP, Preferences | Ports | Set MySQL to 3306 standard</li>
            <li>Login to phpMyAdmin (<a href="http://localhost/phpMyAdminForPHP5/">link for MAMP on localhost</a>)</li>
            <li>Click SQL along the top, then paste in:
              <pre class="code">CREATE DATABASE <b>cameralife</b>;
GRANT ALL ON <b>cameralife</b>.* TO <b>user</b>@<b>localhost</b> IDENTIFIED BY '<b>pass</b>';</pre>
            </li>
          </ul>
        </div>
      </div>

      <div class="well">
        <h2>When your database is set up enter details below</h2>
      </div>

      <form class="form-horizontal" method="post" action="index2.php">
        <div class="control-group">
          <label class="control-label" for="host">Database server</label>
          <div class="controls">
            <input type="text" id="host" name="host" value="localhost">
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="name">Database name</label>
          <div class="controls">
            <input type="text" id="name" name="name" value="cameralife">
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="user">Database user</label>
          <div class="controls">
            <input type="text" id="user" name="user" value="user">
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="pass">Database password</label>
          <div class="controls">
            <input type="password" id="pass" name="pass" value="pass">
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="prefix">Database prefix</label>
          <div class="controls">
            <input type="text" id="prefix" name="prefix" placeholder="(optional)">
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="sitepass">New password for Camera Life</label>
          <div class="controls">
            <input type="password" id="sitepass" name="sitepass" value="">
          </div>
        </div>
        <div class="control-group">
          <div class="controls">
            <button type="submit" class="btn btn-primary btn-large">Continue</button>
          </div>
        </div>
      </form>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min..js"></script>
  </body>
</html>
