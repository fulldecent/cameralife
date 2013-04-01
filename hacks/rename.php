<?php
/**An adminisitrative tool-Batch Renamer that names unnamed photos in a batch
 *
 *The following code handles form actions
 *<code>$curphoto = FALSE;
 *foreach ($_POST as $key=>$val)
 *{
 *  list($cmd, $id) = split('_', $key);
 * if ($id == FALSE) continue;
 * if (!$curphoto || $id != $curphoto->Get('id'))
 *  {
 *   $curphoto = new Photo($id);
 * }
 *</code>
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 */

  $features=array('database','theme','security','imageprocessing','photostore');
  require '../main.inc';
  $cameralife->base_url = dirname($cameralife->base_url);

  $cameralife->Security->authorize('admin_file', 1); // Require

  $start = $_GET['start'] or $start = 0;
  $perpage = $_POST['perpage'] or $perpage = 12;

  // Handle form actions
    $curphoto = FALSE;
  foreach ($_POST as $key=>$val) {
    list($cmd, $id) = explode('_', $key);
    if ($id == FALSE) continue;
    if (!$curphoto || $id != $curphoto->Get('id')) {
      $curphoto = new Photo($id);
    }
    switch ($cmd) {
    case 'desc':
      if ($curphoto->Get('description') != $val && strlen($val) > 0) {
        if ($val == 'ERASED')
          $curphoto->Erase();
        else
          $curphoto->Set('description', $val);
      }
      break;
    case 'key':
      if ($curphoto->Get('keywords') != $val);
        $curphoto->Set('keywords', $val);
      break;
    case 'rot':
      if ($val != 0)
        $curphoto->Rotate($val);
      break;
    case 'stat':
      if ($curphoto->Get('status') != $val);
        $curphoto->Set('status', $val);
      break;
    }
  }

?>
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?> - Batch Renamer</title>
  <link rel="stylesheet" href="hacks.css">
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
  <script language="javascript">
    function changealldesc()
    {
      val = document.getElementById('desc').value;
      inputs = document.getElementsByTagName('input');
      for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].name.indexOf('desc_') >= 0) {
          inputs[i].value=val;
        }
      }
    }
    function addallkey()
    {
      vals = document.getElementById('key').value.split(' ');
      for (var j = 0; j < vals.length; j++) {
        val = vals[j];
        inputs = document.getElementsByTagName('input');
        for (var i = 0; i < inputs.length; i++) {
          if (inputs[i].name.indexOf('key_') >= 0) {
            if ((' '+inputs[i].value+' ').indexOf(' '+val+' ') == -1) {
              if (inputs[i].value.substr(-1) != ' ' && inputs[i].value.length > 0)
                inputs[i].value += ' ';
              inputs[i].value += val;
            }
          }
        }
      }
    }
    function delallkey()
    {
      vals = document.getElementById('key').value.split(' ');
      for (var j = 0; j < vals.length; j++) {
        val = vals[j];
        inputs = document.getElementsByTagName('input');
        for (var i = 0; i < inputs.length; i++) {
          if (inputs[i].name.indexOf('key_') >= 0) {
            re = new RegExp(' '+val+' ');
            if (re.test(' '+inputs[i].value+' ')) {
              newval = (' '+inputs[i].value+' ').replace(re,' ');
              inputs[i].value = newval.substr(1, newval.length-2);
            }
          }
        }
      }
    }
  </script>
</head>
<body>
<form id="form1" method="post"> <!--action="<?= $_SERVER['PHP_SELF'] ?>&#63;start=<?= $_GET['start']?>"-->

<div id="header">
<h1>Hacks - Rename</h1>
<?php
  $home = $cameralife->GetIcon('small');
  echo '<a href="'.$home['href']."\"><img src=\"".$cameralife->IconURL('small-main')."\">".$home['name']."</a>\n";
?> |
<a href="../stats.php"><img src="<?= $cameralife->IconURL('small-photo')?>">Stats</a> |
<a href="../admin/index.php"><img src="<?= $cameralife->IconURL('small-admin')?>">Administration</a>
</div>

<?php
  echo "<p>Show <select name=\"perpage\" onchange=\"document.getElementById('form1').submit()\">";
  foreach (array(8,12,25,100,'all') as $num) {
    if ($perpage == $num)
      echo "<option selected>$num</option>\n";
    else
      echo "<option>$num</option>\n";
  }
  echo "</select> photos per page. <input type=submit value=\"Change\"></p>";

  echo "<table><tr><td>All descriptions:<td><input id='desc' name='desc' size=35><td><input type=button value=\"Change all\" onclick=\"changealldesc()\">";
  echo "<tr><td>All keywords:<td><input id='key' name='key' size=35><td><input type=button value=\"Add to all\" onclick=\"addallkey()\"><input type=button value=\"Remove from all\" onclick=\"delallkey()\"></table>";
  echo "<hr size=1>";

  $selection = "id, description, keywords, status, mtime";
  if ($perpage == 'all') $limit = '';
  else $limit = "LIMIT $start, $perpage";
  $query = $cameralife->Database->Select('photos',$selection,"description='unnamed' AND (status=0 OR status=2)","ORDER BY id $limit");

  echo "<table>";
  while ($photo = $query->FetchAssoc()) {
    if ($i++%2==0) echo "<tr>";
    echo "<td>";
    echo "<a href=\"../photo.php&#63;id=".$photo['id']."\">";
    echo "<img id=\"img_".$photo['id']."\" src='../media.php&#63;scale=thumbnail&amp;id=".$photo['id'].'&amp;ver='.$photo['mtime']."'></a>";
    echo "<td width=\"50%\">";
    echo "<input id=\"desc_".$photo['id']."\" name=\"desc_".$photo['id']."\" value=\"".$photo['description']."\"><br>";
    echo "<input id=\"key_".$photo['id']."\" name=\"key_".$photo['id']."\" value=\"".$photo['keywords']."\"><br> ";
    echo "<input checked type=radio id=\"rot0_".$photo['id']."\" name=\"rot_".$photo['id']."\" value=\"0\" onclick=\"document.getElementById('img_".$photo['id']."').src='rotatethumb.php?id=".$photo['id']."&amp;rotate=0'\">";
    echo "<label for=\"rot0_".$photo['id']."\">N</label>";
    echo "<input type=radio id=\"rot90_".$photo['id']."\" name=\"rot_".$photo['id']."\" value=\"90\" onclick=\"document.getElementById('img_".$photo['id']."').src='rotatethumb.php?id=".$photo['id']."&amp;rotate=90'\">";
    echo "<label for=\"rot90_".$photo['id']."\">R</label>";
    echo "<input type=radio id=\"rot180_".$photo['id']."\" name=\"rot_".$photo['id']."\" value=\"180\" onclick=\"document.getElementById('img_".$photo['id']."').src='rotatethumb.php?id=".$photo['id']."&amp;rotate=180'\">";
    echo "<label for=\"rot180_".$photo['id']."\">U</label>";
    echo "<input type=radio id=\"rot270_".$photo['id']."\" name=\"rot_".$photo['id']."\" value=\"270\" onclick=\"document.getElementById('img_".$photo['id']."').src='rotatethumb.php?id=".$photo['id']."&amp;rotate=270'\">";
    echo "<label for=\"rot270_".$photo['id']."\">L</label><br>";
    $checked = ($photo['status']==0)?'checked':'';
    echo "<input $checked type=radio id=\"stat0_".$photo['id']."\" name=\"stat_".$photo['id']."\" value=\"0\">";
    echo "<label for=\"stat0_".$photo['id']."\">Public</label>";
    $checked = ($photo['status']==2)?'checked':'';
    echo "<input $checked type=radio id=\"stat2_".$photo['id']."\" name=\"stat_".$photo['id']."\" value=\"2\">";
    echo "<label for=\"stat2_".$photo['id']."\">Private</label><br>";
    echo "<input type=button value=\"Erase\" onclick=\"document.getElementById('desc_".$photo['id']."').value='ERASED'\">\n";
    echo "<input type=button value=\"Reset\" onclick=\"document.getElementById('desc_".$photo['id']."').value='".$photo['description']."'\">\n";

  }
  echo "</table>";

  #$cameralife->Theme->Grid($query);
  $total = $cameralife->Database->SelectOne('photos','COUNT(*)',"description='unnamed'");
?>
<p>
  <input type=submit value="Commit Changes">
  <a href="&#63;start=<?= $start ?>">(Undo changes)</a>
</p>
<?php

  for($i=max(0,(floor($_GET['start']/$perpage)-2)*$perpage); $i<min($total,$_GET['start']+3*$perpage); $i+=$perpage)
    if ($i==$_GET['start'])
      echo "Page ".($i/$perpage)." ";
    else
      echo "<a href=\"&#63;start=$i\">Page ".($i/$perpage)."</a>  ";
  echo "$total total photos."
?>
  </table>
  </form>
</body>
</html>
