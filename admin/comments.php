<?php
  # Part of the user manager section:
  # Log analyzer - analyze all logs

  $features=array('database','theme','security');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);

  $cameralife->Security->authorize('admin_customize', 1); // Require

  if (!$_POST['showme'] && !$_POST['showreg'] && !$_POST['showunreg'])
  {
    $_POST['showme'] = TRUE;
    $_POST['showreg'] = TRUE;
    $_POST['showunreg'] = TRUE;
  }
  if ($_POST['showme'] && $_POST['showreg'] && $_POST['showunreg'])
    $showallusers = TRUE;

  if ($_POST['action'] == 'Delete Selected')
  {
    foreach ($_POST as $var => $val)
    {
      if (!is_numeric($var) || !is_numeric($val))
        continue;

      $cameralife->Database->Delete('comments',"id=$var");
    }
  } else if ($_POST['action'] == 'Set Checkpoint') {
    $cameralife->preferences['core']['checkpointcomments'] = $_POST['checkpoint'];
    $cameralife->SavePreferences();
  }
?>

<html>
<head>
  <title><?= $cameralife->preferences['core']['sitename'] ?> - Comment Manager</title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
  <STYLE type="text/css">
    h2{font-size:medium}
  </STYLE>
  <script language="javascript">
  <!--
    function toggleUsers(a,b) 
    {
      document.getElementById(a).style.display = 'block'
      document.getElementById(b).style.display = 'none'
      document.getElementById('showme').checked = 'true'
      document.getElementById('showreg').checked = 'true'
      document.getElementById('showunreg').checked = 'true'
      return false;
    }
  -->
  </script>
</head>
<body>
<form method="post">

<?php
  $menu = array();
  $menu[] = array("name"=>$cameralife->preferences['core']['siteabbr'],
                  "href"=>"../index.php",
                  'image'=>'small-main');
  $menu[] = array("name"=>"Administration",
                  "href"=>"index.php",
                  'image'=>'small-admin');
  $menu[] = array("name"=>"Help with Logs",
                  "href"=>"../setup/checkpoints.html",
                  'image'=>'small-admin');

  $cameralife->Theme->TitleBar("Comment Viewer",
                                'admin',
                                "View and censor comments made on the site",
                                $menu);
?>
  <div id="allusers" <?= ($showallusers) ? '' : 'style="display:none"' ?>>
  <h2>Show changes by anyone <a href="#" onclick="toggleUsers('someusers','allusers')">[change]</a></h2>
  </div>
  <div id="someusers" <?= ($showallusers) ? 'style="display:none"' : '' ?>>
  <h2>Show changes by these users <a href="#" onclick="toggleUsers('allusers','someusers')">[show all]</a></h2>
  <table width="100%">
    <tr>
      <td width="33%">
        <input type="checkbox" id="showme" name="showme"
          <?php if ($_POST["showme"]) echo " checked" ?>
        >
        <label for="showme">
          <?php $cameralife->Theme->Image('small-login') ?>Me
        </label>
      <td width="33%">
        <input type="checkbox" id="showreg" name="showreg"
          <?php if ($_POST["showreg"]) echo " checked" ?>
        >
        <label for="showreg">
          <?php $cameralife->Theme->Image('small-login') ?>Other Registered Users
        </label>
      <td width="33%">
        <input type="checkbox" id="showunreg" name="showunreg"
          <?php if ($_POST["showunreg"]) echo " checked" ?>
        >
        <label for="showunreg">
          <?php $cameralife->Theme->Image('small-login') ?>Unregistered Users
        </label>
  </table>
  </div>
  <h2>Show changes since <span style="color: green">last checkpoint</span></h2>
  <!--
    <select>
      <option>Last checkpoint</option>
      <option>A week ago</option>
      <option>A month ago</option>
      <option>The last 100 changes</option>
    </select>
  -->
  <p><input type=submit value="Query logs"></p>

  <table align="center" cellspacing="2" border=1 width="100%">
    <tr>
      <th colspan=2>Results
  <?php
    $condition = "(0 ";
    if ($_POST['showme']) 
      $condition .= "OR username = '".$cameralife->Security->GetName()."' ";
    if ($_POST['showreg']) 
      $condition .= "OR (username LIKE '_%' AND username != '".$cameralife->Security->GetName()."')";
    if ($_POST['showunreg']) 
      $condition .= "OR username = '' ";
    $condition .= ") ";

    $checkpoint = $cameralife->Database->SelectOne('comments','MAX(id)');
    echo "<input type='hidden' name='checkpoint' value='$checkpoint'>\n";

    $condition .= " AND id > ".($cameralife->preferences['core']['checkpointphotos']+0);
    $extra = "GROUP BY photo_id ORDER BY id DESC";

    $result = $cameralife->Database->Select('comments','*, MAX(id) as maxid',$condition,$extra);
    while($record = $result->FetchAssoc())
    {
      echo "<tr><td align=center>";
      echo "<a href=\"../photo.php&#63;id=".$record['photo_id']."\">";
      $cameralife->Theme->Image('small-photo');
      echo "</a>";
      echo "<br><i>".$record['value_field']."</i>";
      echo "<td>\n";

      $condition = "photo_id = ".$record['photo_id'];
      $result2 = $cameralife->Database->Select('comments','*',$condition, 'ORDER BY id DESC');

      unset($last_row);
      while ($row = $result2->FetchAssoc())
      {
        echo "<input id=\"".(++$htmlid)."\" type=\"checkbox\" name=\"".$record['id']."\" value=\"".$record['id']."\"> ";
        echo "<label style=\"font-weight:bold\" for=\"$htmlid\">".htmlentities($row['comment'])."</label> ";
        echo ($row['username']?$row['username']:'Anonymous').' ('.$row['user_ip'].') '.$row['user_date']."\n";
        echo "<br>";
        $last_row = $row;
      }
    }
  ?>
  </table>

  <p>
        <input type=submit name="action" value="Delete Selected">
        <a href="logs.php">(Revert to last saved)</a><br>
        <input type=submit name="action" value="Set Checkpoint">
        (do this after committing any changes)
  </p>
  </form>
</body>
</html>



