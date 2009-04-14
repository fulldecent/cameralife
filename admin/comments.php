<?php
  # Part of the user manager section:
  # Log analyzer - analyze all logs
/**This is a part of the user manager section.
*Displays the administration page for user input from both registered and unregistered users
*The log analyzer -analyzes all the logs.
*It shows changes since
  *<ul><li>Last checkpoint</li>
  * <li>A week ago</li>
  * <li>A month ago</li>
    *<li> The last 100 changes</li>
    *</ul>
*@link http://fdcl.sourceforge.net
 *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@copyright © 2001-2009 Will Entriken
  *@access public
  */
  /**
  */
  $features=array('database','security', 'photostore');
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
  }
?>
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <link rel="stylesheet" href="admin.css">
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
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

<div id="header">
<h1>Site Administration &ndash; Log Viewer</h1>
<?php
  $home = $cameralife->GetIcon('small');
  echo '<a href="'.$home['href']."\"><img src=\"".$cameralife->IconURL('small-main')."\">".$home['name']."</a>\n";
?> |
<a href="index.php"><img src="<?= $cameralife->IconURL('small-admin')?>">Site Administration</a>  |
<a href="<?= $cameralife->base_url ?>/setup/checkpoints.html">Help with checkpoints</a>
</div>

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
          <img src="<?= $cameralife->IconURL('small-login') ?>">Me
        </label>
      <td width="33%">
        <input type="checkbox" id="showreg" name="showreg"
          <?php if ($_POST["showreg"]) echo " checked" ?>
        >
        <label for="showreg">
          <img src="<?= $cameralife->IconURL('small-login') ?>">Other Registered Users
        </label>
      <td width="33%">
        <input type="checkbox" id="showunreg" name="showunreg"
          <?php if ($_POST["showunreg"]) echo " checked" ?>
        >
        <label for="showunreg">
          <img src="<?= $cameralife->IconURL('small-login') ?>">Unregistered Users
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

  <form method="post">
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

    $condition .= " AND id > ".($cameralife->GetPref('checkpointcomments')+0);
    $extra = "GROUP BY photo_id ORDER BY id DESC";

    $result = $cameralife->Database->Select('comments','*, MAX(id) as maxid',$condition,$extra);
    while($record = $result->FetchAssoc())
    {
      $photo = new Photo($record['photo_id']);
      $icon = $photo->GetIcon('small');

      echo "<tr><td align=center>";
      echo "<a href=\"".$icon['href']."\">";
      echo "<img src=\"".$cameralife->IconURL('small-photo')."\">";
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
  </p>
  </form>

<h2>Update checkpoint</h2>
<form method="post" action="controller_prefs.php">
<p>
  All of these logs will be hidden when you visit this page later<br>
  <input type="hidden" name="target" value="<?= $_SERVER['PHP_SELF'] ?>" />
  <input type="hidden" name="module1" value="CameraLife" />
  <input type="hidden" name="param1" value="checkpointcomments" />
  <input type="hidden" name="value1" value="<?= $checkpoint ?>">
  <input type="submit" value="Update checkpoint">
</p>
</form>
</body>
</html>

