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

  $_GET['page'] or $_GET['page'] = 'users';

  foreach ($_POST as $key => $val) {
    if ($val == "delete")
      $cameralife->Database->Delete('users',"id='$key'");
    else
      $cameralife->Database->Update('users',array('auth'=>$val),"id='$key'");
  }
  $cameralife->savePreferences();

  public function html_select_auth($param_name)
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
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <link rel="stylesheet" href="../../../admin/admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
  <script language="javascript">
    public function changeall()
    {
      val = document.getElementById('status').value;
      inputs = document.getElementsByTagName('select');
      for (var i = 0; i < inputs.length; i++) {
          inputs[i].value=val;
      }
    }
  </script>
</head>
<body>

<div id="header">
<h1>Site Administration &ndash; Security Manager</h1>
<?php
$homeOpenGraph = $cameralife->getOpenGraph();
echo '<a href="'.htmlspecialchars($homeOpenGraph['og:url'])."\"><img src=\"".htmlspecialchars($homeOpenGraph['og:image']).htmlentities($homeOpenGraph['title'])."</a>\n";
?> |
<a href="../../../admin/index.php"><img src="<?= $cameralife->iconURL('small-admin')?>">Site Administration</a>
</div>

<h2>
  Show:
  <a href="?page=users">Users</a> |
  <a href="?page=policies">Policies</a>
</h2>

<?php
  if ($_GET['page'] == 'users') {
?>
    <form method="post">
    <table align="center" cellspacing="2" border=1 width="100%">
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
        echo '<img src="'.$cameralife->iconURL('small-login').'">';
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
    <table align="center" cellspacing="2" border=1 width="100%">
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
  <input type="submit" value="Commit Changes">
  <a href="users.php">(Revert to last saved)</a>
</p>

</form>
</body>
</html>
