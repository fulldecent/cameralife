
<?php
/**
* Displays post installation notifcation messages
*@link http://fdcl.sourceforge.net
*@version 2.6.3b6
*@author Will Entriken <cameralife@phor.net>
*@copyright Copyright (c) 2001-2009 Will Entriken
*@access public
*/
/**
*/

  $version = '2.6.0b3';
  $continue = true;

  if(file_exists('../modules/config.inc'))
  {
    die("Camera Life already appears to be set up, because modules/config.inc exists.");
  }
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="common.css">
<title>Camera Life Installation</title>
</head>
<body>

<img src="images/intro1.png" align=center>

<h1>Welcome to Camera Life</h1>

Thank you for choosing to install Camera Life. We hope you will find this software is easy to use and fun. This project is licensed under the the GNU General Public License, version 2. If you need help, look in:
<ul>
<li><a href="../INSTALL">The INSTALL file</a>
<li><a href="http://fdcl.sourceforge.net">The Camera Life project homepage</a>
<li>Email <a href="mailto:cameralife<?php echo '@' ?>phor.net">cameralife<?php echo '@' ?>phor.net</a>
</ul>
If you are upgrading from a previous version of Camera Life, stop and read the file <a href="../UPGRADE">UPGADE</a>.

<h2>Checking Prerequisites...</h2>

<p />
<table width="90%" align=center>
  <tr>
    <td width="70%">
      Checking for MySQL support...
    <td width="30%">
      <?php
        if (function_exists('mysql_query'))
          echo "<font color=green>Installed</font>\n";
        else
        {
          echo "<font color=red>Error</font>
                <tr><td colspan=2><p class='important'>You do not appear to have MySQL installed, ".
               "see http://php.net/manual/en/ref.mysql.php for more info</p>\n";
          $continue = false;
        }
      ?>
  <tr>
    <td>
      Checking for GD support...
    <td>
      <?php
        if (function_exists('gd_info'))
          echo "<font color=green>Installed</font>\n";
        else
        {
          echo "<font color=red>Error</font>
                <tr><td colspan=2><p class='important'>You do not appear to have GD installed, ".
               "see http://php.net/manual/en/ref.image.php for more info, or on Ubuntu use: apt-get install php5-gd; reboot</p>\n";
          $continue = false;
        }
      ?>
  <tr>
    <td>
      Checking for JPEG support...
    <td>
      <?php
        $info = gd_info();

        if ($info['JPG Support'] || $info['JPEG Support'])
          echo "<font color=green>GD supports JPEG</font>\n";
        else
        {
          echo "<font color=red>Error</font>
                <tr><td colspan=2><p class='important'>Your version of GD does not support JPEG, see ".
              "http://us4.php.net/manual/en/ref.image.php for more info here's some ".
              "info about your GD, I hope it helps:</p><br><pre>".print_r($info,true)."</pre>\n";
          $continue = false;
        }
      ?>
  <tr>
    <td>
      Checking Magic Quotes...
    <td>
      <?php
        if (get_magic_quotes_gpc() )
          echo "<font color=orange>Warning</font>
                <tr><td colspan=2><p class='important'>You have Magic quotes enabled. This is 
                deprecated. For details, please see 
                http://www.php.net/manual/en/info.configuration.php#ini.magic-quotes-gpc</font>\n";
        else
        {
          echo "<font color=green>Configured correctly</font>\n";
        }
      ?>
  <tr>
    <td>
      Checking for content negotiation...
    <td>
      <?php
        $url = 'http://' . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.$_SERVER['SERVER_PORT']). str_replace('index.php','',$_SERVER['PHP_SELF']) . 'images/blank.gif';
        $fh = fopen($url, 'r');
        while ($fh && !feof($fh))
        {
          $data .= fread($fh, 8192);
        }

        if (md5($data) == 'accba0b69f352b4c9440f05891b015c5')
          echo "<font color=green>Configured correctly</font>\n";
        else
        {
          echo "<font color=red>Error</font>
                <tr><td colspan=2><p class='important'>Your server doesn't appear to support
                CONTENT NEGOTIATION, or is not allowing HTACCESS OVERRIDES. Or maybe the file .htaccess
                was not copied. You're going to need to fix that before you continue.</p>\n";
          $continue = false;
        }
      ?>
  <tr>
    <td>
      Checking for mod_rewrite...
    <td>
      <?php
        $url = 'http://' . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.$_SERVER['SERVER_PORT']). str_replace('index.php','',$_SERVER['PHP_SELF']) . 'images/clear.gif';
        if(@fopen($url, 'r'))
        {
          echo "<font color=orange>Warning</font>
                <tr><td colspan=2><p class='important'>Optional: Enable MOD REWRITE in your web server to use pretty URLs.</p>\n";
        } else {
          echo "<font color=green>Configured correctly</font>\n";
        }
      ?>
  <tr>
    <td>
      Checking package permissions...
    <td>
      <?php
        $writable = array();
        $writable[] = array('modules','you will need to manually paste a file in there later',1);
        $writable[] = array('images/photos','you will not be able to upload photos from inside Camera Life',1);
        $writable[] = array('images/cache','',2);
        $writable[] = array('images/deleted','',2);
        $allerrors = 0;
        $unwritable = array();

        foreach ($writable as $a)
        {
          if(!is_writable(dirname(dirname(__FILE__)).'/'.$a[0]))
          {
            $allerrors = max($allerrors, $a[2]);
            $unwritable[] = $a;
          }
        }

        if ($allerrors == 2)
        {
          echo "<font color=red>Error</font>";
          $continue = false;
        }
        elseif ($allerrors == 1)
        {
          echo "<font color=orange>Warning</font>";
        }
        else
        {
          echo "<font color=green>Configured correctly</font>";
        }

        if (count($unwritable))
        {
          echo "<tr><td colspan=2><p class='important'>Some directories should be editable but aren't (mouseover each for details): ";
          foreach ($unwritable as $a)
          {
            if ($i++) echo ', ';
            if ($a[2] == 1)
              echo "<a title=\"(optional) if you don't fix this ".$a[1]."\">".$a[0]."</a>";
            if ($a[2] == 2)
              echo "<a title=\"you must fix this to continue\">".$a[0]."</a>";
          }
          echo ". <a href =\"index.php\">Check again</a>. <br />";
          foreach ($unwritable as $a)
            echo "<br /><tt>chmod 777 ".dirname(dirname(__FILE__)).'/'.$a[0]."</tt>\n";
        }
?>
  <tr>
    <td>
      Checking .htaccess...
    <td>
<?php
  if (file_exists('../.htaccess') && !is_writable('../.htaccess'))
  {
    echo "<font color=orange>Warning</font>
          <tr><td colspan=2><p class='important'>Your file .htaccess exists but is not writable. Please fix this now, or you will have to edit the file manually later. <a href=\"index.php\">Check again</a>. ";
    echo "<br /><br /><tt>chmod 777 ".dirname(dirname(__FILE__))."/.htaccess</tt>";
    echo "</p>\n";
  } elseif (!copy ('example.htaccess', '../.htaccess')) {
    echo "<font color=orange>Warning</font>
          <tr><td colspan=2><p class='important'>We cannot automatically edit .htaccess. Please fix this now, or you will have to edit the file manually later. <a href=\"index.php\">Check again</a>. ";
    echo "<br /><br /><tt>cp ".dirname(__FILE__)."/example.htaccess ".dirname(dirname(__FILE__))."/.htaccess";
    echo "<br />chmod 777 ".dirname(dirname(__FILE__))."/.htaccess</tt>";
    echo "</p>\n";
  } else {
    echo "<font color=green>Configured correctly</font>\n";
  }


?>

  <tr>
    <td>
      Checking package contents...
    <td>
<?php
        if(!file_exists('.htaccess'))
        {
          echo "<font color=orange>Error</font>
                <tr><td colspan=2><p class='important'>You are missing the file <b>.htaccess</b> from the
                package. Maybe the file was not copied from the package. Please ensure this file is
                in the main install directory.";
          $continue = false;
        }
        else
        {
          echo "<font color=green>Configured correctly</font>";
        }

  if (!file_exists('../.svn'))
  {

      ?>
  <tr>
    <td>
      Checking package version...
    <td>
      <?php
        $main = file('../main.inc');
        $versionline = preg_grep('/this..version/', $main);
        preg_match("/'(.*)'/", join($versionline), $matches);

        # We collect your ip and version
        $newest = file_get_contents('http://fdcl.sourceforge.net/check.php?i='.$matches[1]);

        if ($matches[1] == $newest)
          echo "<font color=green>You have ".$matches[1]."</font>";
        else
        {
          echo "<font color=red>Error</font>
                <tr><td colspan=2><p class='important'>You are installing Camera Life ".$matches[1].", but the 
                  latest released version is $newest. The latest version can be downloaded 
                  at <a href=\"http://fdcl.sourceforge.net\">http://fdcl.sourceforge.net</a>.</p>";
        }
  }

  if (!file_exists('../.svn'))
  {

      ?>
  <tr>
    <td>
      Checking package version...
    <td>
      <?php
        $main = file('../main.inc');
        $versionline = preg_grep('/this..version/', $main);
        preg_match("/'(.*)'/", join($versionline), $matches);

        # We collect your ip and version
        $newest = file_get_contents('http://fdcl.sourceforge.net/check.php?i='.$matches[1]);

        if ($matches[1] == $newest)
          echo "<font color=green>You have ".$matches[1]."</font>";
        else
        {
          echo "<font color=red>Error</font>
                <tr><td colspan=2><p class='important'>You are installing Camera Life ".$matches[1].", but the 
                  latest released version is $newest. The latest version can be downloaded 
                  at <a href=\"http://fdcl.sourceforge.net\">http://fdcl.sourceforge.net</a>.</p>";
        }
  }
      ?>

</table>

<?php
  if ($continue == false)
  {
    echo "<p class='important'>The prerequisites have not been met. Fix them, and <a href =\"index.php\">Check again</a>.</p>";
    echo "</body></html>";
    exit(0);
  }
?>

  <h2>Create a database</h2> 
 
  Please set up a MySQL database and create a user with ALL PRIVILEGES for Camera Life.
 
  <p>For Linux:</p> 
 
  <pre class="code">$ sudo mysql
mysql&lt; CREATE DATABASE <b>cameralife</b>;
mysql&lt; GRANT ALL ON <b>cameralife</b>.* TO <b>user</b>@<b>localhost</b> IDENTIFIED BY '<b>pass</b>';</pre> 
 
  <p>Using cPanel:</p> 
 
  <ul> 
    <li><a target="_new" href="http://phor.net/cpanel">Login to cPanel</a></li> 
    <li>Click <a target="_new" href="http://phor.net:2082/frontend/x3/sql/index.html">MySQL Databases</a></li> 
    <li>Create Database: enter <b>cameralife</b>, read what your database is actually named, go back</li> 
    <li>Add New User: <b>username</b> <b>password</b></li> 
    <li>Add User To Database: select your user and database, and tick ALL PRIVILEGES</li> 
    <li>Note, your cPanel account name will proceed your database and user names below. For example, your database name will be mycpanelname_cameralife</li> 
  </ul> 
 
  <p>Using phpMyAdmin or MAMP:</p> 
  <ul> 
    <li>If using MAMP, Preferences | Ports | Set MySQL to 3306 standard</li> 
    <li>Login to phpMyAdmin (<a href="http://localhost/phpMyAdminForPHP5/">link for MAMP on localhost</a>)</li> 
    <li>Click SQL along the top, then paste in:
      <pre class="code">CREATE DATABASE <b>cameralife</b>;
GRANT ALL ON <b>cameralife</b>.* TO <b>user</b>@<b>localhost</b> IDENTIFIED BY '<b>pass</b>';</pre> 
    </li> 
  </ul> 
 
  <p>Note: If you setup Camera Life on a different system, please tell us about it at cameralife@phor.net</p> 
 
  <h2>Initialize database</h2> 
 
  Provide the credentials you chose above so Camera Life may access your new database. Also choose the admin password for your new website.
 
  <br><br> 
 
  <table> 
  <form action="index2.php" method=POST> 
  <tr><td>Database server:<td> <input type="text" name="host" value="localhost"> 
  <tr><td>Database name:<td> <input type="text" name="name" value="cameralife"> 
  <tr><td>Database user:<td> <input type="text" name="user" value="user"> 
  <tr><td>Database pass:<td> <input type="password" name="pass" value=""> 
  <tr><td>Database table name prefix (optional):<td> <input type="text" name="prefix" value=""> 
  <tr><td>&nbsp;
  <tr><td>New password for Camera Life <b>admin</b> user:<td> <input type="password" name="sitepass" value=""> 
  </table> 
 
  <center> 
    <input class="pagelink" type="submit" value="Continue --&gt;"> 
  </center> 
  </form> 
 

</body>
</html>
