<?php
  $version = '2.6.0';
  $continue = true;
?>

<html>
<head>
<link rel="stylesheet" type="text/css" href="common.css">
<title>Camera Life Installation</title>
</head>
<body>

<img src="images/fdcl.jpg" alt='logo'>

<h1>Welcome to Camera Life</h1>

Thank you for choosing to install Camera Life. I hope you will find this project is easy to use and fun. This project is released under the terms of the GNU General Public License, version 2. If you need help, look in:
<ul>
<li><a href="../README">The README</a>
<li><a href="http://fdcl.sourceforge.net">The project webpage</a>
<li>Hit me up: <a href="mailto:cameralife@phor.net">cameralife@phor.net</a>
</ul>
If you are upgrading from a previous version of Camera Life, stop and read the file <a href="../UPGRADE">UPGADE</a>.

<h2>Prerequisites</h2>

<p />
<table width="90%" align=center>
  <tr>
    <td width="70%">
      Checking for MySQL support...
    <td width="30%">
      <?php
        if (function_exists('mysql_query'))
          echo "<font color=green>Installed</font>";
        else
        {
          echo "<font color=red>Error</font>
                <tr><td colspan=2><p class='important'>You do not appear to have MySQL installed, ".
               "see http://php.net/manual/en/ref.mysql.php for more info</p>";
          $continue = false;
        }
      ?>
  <tr>
    <td>
      Checking for GD support...
    <td>
      <?php 
        if (function_exists('gd_info'))
          echo "<font color=green>Installed</font>";
        else
        {
          echo "<font color=red>Error</font>
                <tr><td colspan=2><p class='important'>You do not appear to have GD installed, ".
               "see http://us4.php.net/manual/en/ref.image.php for more info</p>";
          $continue = false;
        }
      ?>
  <tr>
    <td>
      Checking for JPEG support...
    <td>      
      <?php
        $info = gd_info();

        if ($info['JPG Support'])
          echo "<font color=green>GD supports JPEG</font>";
        else
        {
          echo "<font color=red>Error</font>
                <tr><td colspan=2><p class='important'>Your version of GD does not support JPEG, see ".
              "http://us4.php.net/manual/en/ref.image.php for more info here's some ".
              "info about your GD, I hope it helps:</p><br><pre>".print_r($info,true)."</pre>";
          $continue = false;
        }
      ?>
  <tr>
    <td>
      Checking Magic Quotes...
    <td>
      <?php
        if (get_magic_quotes_gpc())
          echo "<font color=green>Configured correctly</font>";
        else
        {
          echo "<font color=orange>Warning</font>
                <tr><td colspan=2><p class='important'>Magic quotes are disabled, you may want this, see ".
               "http://us4.php.net/manual/en/ref.info.php#ini.magic-quotes-gpc for more info</font>";
        }
      ?>
  <tr>
    <td>
      Checking for content negotiation...
    <td>
      <font color=green>Successful</font>
      <img height=10 width=50 src="images/blank" alt="Your server doesn't appear to support
        CONTENT NEGOTIATION, or is not allowing HTACCESS OVERRIDES. Or maybe the file .htaccess 
        was not copied. You're going to need to fix that before you continue. ">
  <tr>
    <td>
      Checking package permissions...
    <td>
      <?php
        if (!is_writable('../modules/database/mysql/config.inc'))
        {
          echo "<font color=orange>Warning</font>
                <tr><td colspan=2><p class='important'>The file modules/database/mysql/config.inc 
                is not writable by the webserver. If you fix this, setup will be faster, otherwise, 
                you will need to edit this file manually later.";
        }
        elseif(file_exists('../.htaccess'))
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
      ?>
</table>

<?php
        if ($continue == false)
        {
          echo "<p class='important'>The prerequisites have not been met. Fix them, and reload this page.</p>";
        }else{
          echo '<p align=center>
                <a class="pagelink" href="index2.php"><img src="images/continue.jpg" alt="continue"></a>
                </p>';
        }
?>
</body>
</html>
