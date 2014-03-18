<?php
  /** Enables administration of site activities and settings
  *
  *<ul>
  *<li>Allows monitoring of user activities</li>
  *<li>Enables editing of security settings</li>
  *</ul>
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */
  /**
*/

  $features=array('security');
  require '../../../main.inc';
  $cameralife->base_url = dirname(dirname(dirname($cameralife->base_url)));

  $cameralife->Security->authorize('admin_customize', 1); //require

  $_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 'users';

  foreach ($_POST as $key => $val) {
    if ($val == "delete")
      $cameralife->Database->Delete('users',"id='$key'");
    else
      $cameralife->Database->Update('users',array('auth'=>$val),"id='$key'");
  }
  $cameralife->SavePreferences();

  function html_select_auth($param_name)
  {
    global $cameralife;
    global $prefnum;
    $prefnum++;

    echo "      <input type=\"hidden\" name=\"module$prefnum\" value=\"".get_class($cameralife->Security)."\" />\n";
    echo "      <input type=\"hidden\" name=\"param$prefnum\" value=\"".$param_name."\" />\n";
    echo "      <select name=\"value$prefnum\">\n";
    if ($cameralife->Security->GetPref($param_name) == 0)
      echo "  <option selected value=\"0\">Anyone</option>\n";
    else
      echo "  <option value=\"0\">Anyone</option>\n";
    if ($cameralife->Security->GetPref($param_name) == 1)
      echo "  <option selected value=\"1\">Unconfirmed registration</option>\n";
    else
      echo "  <option value=\"1\">Unconfirmed registration</option>\n";
    if ($cameralife->Security->GetPref($param_name) == 2)
      echo "  <option selected value=\"2\">Confirmed registration</option>\n";
    else
      echo "  <option value=\"2\">Confirmed registration</option>\n";
    if ($cameralife->Security->GetPref($param_name) == 3)
      echo "  <option selected value=\"3\">Privileged account</option>\n";
    else
      echo "  <option value=\"3\">Priviliged account</option>\n";
    if ($cameralife->Security->GetPref($param_name) == 4)
      echo "  <option selected value=\"4\">Administrator</option>\n";
    else
      echo "  <option value=\"4\">Administrator</option>\n";
    if ($cameralife->Security->GetPref($param_name) == 5)
      echo "  <option selected value=\"5\">Owner</option>\n";
    else
      echo "  <option value=\"5\">Owner</option>\n";
    echo "</select>\n";
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?= $cameralife->GetPref('sitename') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="../../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    <link href="../bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script language="javascript">
      public function changeall() {
        val = document.getElementById('status').value;
        inputs = document.getElementsByTagName('select');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].value=val;
        }
      }
    </script>
  </head>
  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <span class="brand"><a href="../../../"><?= $cameralife->GetPref('sitename') ?></a> / Administration</span>
        </div>
      </div>
    </div>
    <div class="container">

      <h1>Security Manager (openid)</h1>

      <ul class="nav nav-tabs">
<?php
foreach (array('users'=>'Users', 'policies'=>'Policies') as $id=>$name) {
  $class = $_GET['page'] == $id ? 'active' : '';
  echo "        <li class=\"$class\"><a href=\"?page=$id\">$name</a></li>\n";
}
?>
      </ul>

<?php
  if ($_GET['page'] == 'users') {
?>
    <form method="post">
    <table class="table">
      <tr>
        <th width="16%">User
        <th width="16%">Group
        <th width="16%">Last online
        <th width="16%">IP Address
        <th width="10%">Actions
        <th width="10%">Uploads
        <th width="10%">Options
    <?php
      $result = $cameralife->Database->Select('users','*',NULL, 'ORDER BY auth desc');
      while ($curuser = $result->FetchAssoc()) {
        $count_actions = $cameralife->Database->SelectOne('logs','COUNT(*)',"user_name='".$curuser["username"]."'");
        $count_photos = $cameralife->Database->SelectOne('photos','COUNT(*)',"username='".$curuser["username"]."'");

        echo "<tr><td>\n";
        echo '<img src="'.$cameralife->IconURL('small-login').'">';
        echo $curuser["username"]."\n";
        echo "  <td><select name=\"".$curuser["id"]."\">\n";
        if($curuser["auth"] == 1)
          echo "      <option selected value=\"1\">1 - Unconfirmed</option>\n";
        else
          echo "      <option value=\"1\">1 - Unconfirmed</option>\n";
        if($curuser["auth"] == 2)
          echo "      <option selected value=\"2\">2 - Confirmed</option>\n";
        else
          echo "      <option value=\"2\">2 - Confirmed</option>\n";
        if($curuser["auth"] == 3)
          echo "      <option selected value=\"3\">3 - Privileged</option>\n";
        else
          echo "      <option value=\"3\">3 - Privileged</option>\n";
        if($curuser["auth"] == 4)
          echo "      <option selected value=\"4\">4 - Administrator</option>\n";
        else
          echo "      <option value=\"4\">4 - Administrator</option>\n";
        if($curuser["auth"] >= 5)
          echo "      <option selected value=\"5\">5 - Owner</option>\n";
        else
          echo "      <option value=\"5\">5 - Owner</option>\n";
        echo "    </select>\n";
        echo "  <td>".$curuser["last_online"]."\n";
        echo "  <td>".$curuser["last_ip"]."\n";
        echo "  <td>".$count_actions."\n";
        echo "  <td>".$count_photos."\n";
        echo "  <td align=middle>\n";
        if($curuser["auth"] >= 5)
          echo "    <input type=checkbox disabled name=\"".$curuser["id"]."\" value=\"delete\">\n";
        else
          echo "    <input type=checkbox name=\"".$curuser["id"]."\" value=\"delete\">";
        echo "&nbsp;Delete\n";
      }
    ?>
    </table>

<?php } elseif ($_GET['page'] == 'policies') { ?>
    <form method="post" action="<?= $cameralife->base_url . '/admin/controller_prefs.php' ?>">
    <input type="hidden" name="target" value="<?= $cameralife->base_url .'/modules/security/'.$cameralife->GetPref('security').'/administer.php' ?>&#63;page=<?= $_GET['page'] ?>">
    <table class="table">
      <tr>
        <th colspan=2>
          Permissions - <i>the minimum user class required to perform certain actions</i>
      <tr>
        <td>Edit photo descriptions
        <td width=100><?php html_select_auth("auth_photo_rename") ?>
      <tr>
        <td>Delete photos (can be easily restored in file manager)
        <td><?php html_select_auth("auth_photo_delete") ?>
      <tr>
        <td>Upload photos
        <td><?php html_select_auth("auth_photo_upload") ?>
      <tr>
        <td>Modify photos (rotate, crop, resize...)
        <td><?php html_select_auth("auth_photo_modify") ?>
      <tr>
        <td>Change and add albums and topics
        <td><?php html_select_auth("auth_admin_albums") ?>
      <tr>
        <td>Administer file manager
        <td><?php html_select_auth("auth_admin_file") ?>
      <tr>
        <td>Administer theme manager (effects entire site)
        <td><?php html_select_auth("auth_admin_theme") ?>
      <tr>
        <td>Upper administation (users, customize, register...)
        <td><?php html_select_auth("auth_admin_customize") ?>
    </table>
<?php } ?>

<p>
  <input type="submit" value="Commit Changes" class="btn btn-primary">
  <a href="users.php" class="btn">Revert to last saved</a>
</p>

</form>
</body>
</html>
