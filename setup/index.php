
<?php
/**
* Displays post installation notifcation messages
*@link http://fdcl.sourceforge.net
*@version 2.6.3b5
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

Thank you for choosing to install Camera Life. We hope you will find this project is easy to use and fun. This project is released under the terms of the GNU General Public License, version 2. If you need help, look in:
<ul>
<li><a href="../README">The README</a>
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
        if (get_magic_quotes_gpc())
          echo "<font color=green>Configured correctly</font>\n";
        else
        {
          echo "<font color=orange>Warning</font>
                <tr><td colspan=2><p class='important'>Magic quotes are disabled, you may want this, see ".
               "http://us4.php.net/manual/en/ref.info.php#ini.magic-quotes-gpc for more info</font>\n";
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
        $url = 'http://' . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.$_SERVER['SERVER_PORT']). str_replace('index.php','',$_SERVER['PHP_SELF']) . '../testrewrite';
        $fh = @fopen($url, 'r');
        while ($fh && !feof($fh))
        {
          $data2 .= fread($fh, 8192);
        }
        if (md5($data2) == 'accba0b69f352b4c9440f05891b015c5')
          echo "<font color=green>Configured correctly</font>\n";
        else
        {
          echo "<font color=orange>Warning</font>
                <tr><td colspan=2><p class='important'>Optional: Enable MOD REWRITE in your web server to use pretty URLs.</p>\n";
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
      Checking package contents...
    <td>
<?php
        if(!file_exists('../.htaccess'))
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
        }else{
          echo '<p align=center>
                <a class="pagelink" href="index2.php">Continue --&gt;</a>
                </p>';
        }
?>
</body>
</html>
