<?php
  # In charge of monitoring users and security settings

  $features=array('database','theme','security');
  require "../../../main.inc";
  $base_url = dirname(dirname(dirname($base_url)));

  $cameralife->Security->authorize('admin_customize', 1); //require

  $_GET['page'] or $_GET['page'] = 'users';

  foreach ($_POST as $key => $val)
  {
    if ($_GET['page'] == 'users')
    {
      if ($val == "delete")
        $cameralife->Database->Delete('users',"username='$key'");
      else
        $cameralife->Database->Update('users',array('auth'=>$val),"username='$key'");
    }
    else if ($_GET['page'] == 'policies')
    {
      $cameralife->preferences['defaultsecurity'][$key] = $val;
    }
  }
  $cameralife->SavePreferences();

  function html_select_auth($param_name)
  {
    global $cameralife;

    echo "\n<select name=\"$param_name\">\n";
    if ($cameralife->preferences['defaultsecurity'][$param_name] == 0)
      echo "  <option selected value=\"0\">Anyone</option>\n";
    else
      echo "  <option value=\"0\">Anyone</option>\n";
    if ($cameralife->preferences['defaultsecurity'][$param_name] == 1)
      echo "  <option selected value=\"1\">Unconfirmed registration</option>\n";
    else
      echo "  <option value=\"1\">Unconfirmed registration</option>\n";
    if ($cameralife->preferences['defaultsecurity'][$param_name] == 2)
      echo "  <option selected value=\"2\">Confirmed registration</option>\n";
    else
      echo "  <option value=\"2\">Confirmed registration</option>\n";
    if ($cameralife->preferences['defaultsecurity'][$param_name] == 3)
      echo "  <option selected value=\"3\">Privileged account</option>\n";
    else
      echo "  <option value=\"3\">Priviliged account</option>\n";
    if ($cameralife->preferences['defaultsecurity'][$param_name] == 4)
      echo "  <option selected value=\"4\">Administrator</option>\n";
    else
      echo "  <option value=\"4\">Administrator</option>\n";
    if ($cameralife->preferences['defaultsecurity'][$param_name] == 5)
      echo "  <option selected value=\"5\">Owner</option>\n";
    else
      echo "  <option value=\"5\">Owner</option>\n";
    echo "</select>\n";
  }
?>

<html>
<head>
  <title><?= $cameralife->preferences['core']['sitename'] ?> - User Manager</title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
</head>
<body>
<form method="post">

<?php
  $menu = array();
  $menu[] = array("name"=>$cameralife->preferences['core']['siteabbr'],
                  "href"=>"$base_url/index.php",
                  'image'=>'small-main');
  $menu[] = array("name"=>"Administration",
                  "href"=>"$base_url/admin/index.php",
                  'image'=>'small-admin');

  $cameralife->Theme->TitleBar("User Manager",
                               'admin',
                               "Modify, confirm and delete users",
                               $menu);

  $sections[] = array('name'=>'Users',
                      'page_name'=>'users',
                      'image'=>'small-login');
  $sections[] = array('name'=>'Policies',
                      'page_name'=>'policies',
                      'image'=>'small-login');

  $cameralife->Theme->MultiSection($sections);

  if ($_GET['page'] == 'users' ) 
  {
?>
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
      while ($curuser = $result->FetchAssoc())
      {
        $count_actions = $cameralife->Database->SelectOne('logs','COUNT(*)',"user_name='".$curuser["username"]."'");
        $count_photos = $cameralife->Database->SelectOne('photos','COUNT(*)',"username='".$curuser["username"]."'");
  
        echo "<tr><td>\n";
        $cameralife->Theme->Image('small-login',array('align'=>'middle'));
        echo $curuser["username"]."\n";
        echo "  <td><select name=\"".$curuser["username"]."\">\n";
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
          echo "    <input type=checkbox disabled name=\"".$curuser["username"]."\" value=\"delete\">\n";
        else
          echo "    <input type=checkbox name=\"".$curuser["username"]."\" value=\"delete\">";
        echo "&nbsp;Delete\n";
      }
    ?>
    </table>
<?php } else if ($_GET['page'] == 'policies' ) { ?>
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
  <input type=submit value="Commit Changes">
  <a href="users.php">(Revert to last saved)</a>
</p>

</form>
</body>
</html>


