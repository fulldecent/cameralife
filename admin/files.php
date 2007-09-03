<?php
  # Handle file things
  # BEWARE: DUMP YOUR DATABASE BEFORE FUCKING AROUND HERE!
  # ... if only I had told myself that earlier today :-(

  @ini_set('max_execution_time',9000);

  $features=array('database','theme','security','imageprocessing');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);
  chdir ($cameralife->base_dir);

  $cameralife->Security->authorize('admin_file', 1); // Require

  $_GET['page'] or $_GET['page'] = 'flagged';

  // Handle form actions
  foreach ($_POST as $key=>$val)
  {
    if (!is_int($key)) continue;
    $curphoto = new Photo($key);
    if ($val>=0 && $val<=3)
      $curphoto->Set('status', $val);
    else // Erased file
      $curphoto->Erase();
  }

  // Returns an array of files starting at $path
  // in the form 'path'=>basename(path)
  function walk_dir($path)
  {
    $retval = array();
    if ($dir = opendir($path)) {
      while (false !== ($file = readdir($dir)))
      {
        if ($file[0]==".") continue;
        if (is_dir($path."/".$file))
          $retval = array_merge($retval,walk_dir($path."/".$file));
        else if (is_file($path."/".$file))
          if (preg_match("/.jpg$/i",$file))
            $retval[$path."/".$file] = $file;
          else
            echo "Skipped $path/$file, not a JPEG file<br>\n";
      }
      closedir($dir);
    }
    return $retval;
  }
?>

<html>
<head>
  <title><?= $cameralife->preferences['core']['sitename'] ?> - File Manager</title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
  <script language="javascript">
    function changeall() {
      val = document.getElementById('status').value;
      inputs = document.getElementsByTagName('select');
      for (var i = 0; i < inputs.length; i++) {
          inputs[i].value=val;
      }
    }
  </script>
</head>
<body>
<form method="post" action="http://<?= $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'] ?>&#63;page=<?= $_GET['page']?>">

<?php
  $menu = array();

  $menu[] = $icon = $cameralife->GetSmallIcon();

  $menu[] = array("name"=>"Administration",
                  "href"=>"index.php",
                  'image'=>'small-admin');

  $cameralife->Theme->TitleBar('File Manager', 
                               'admin',
                               'Manage flagged and hidden photos',
                               $menu);

  $sections[] = array('name'=>'Flagged Photos','page_name'=>'flagged');
  if ($cameralife->Database->SelectOne('photos','COUNT(*)','status=2'))
    $sections[] = array('name'=>'Private Photos','page_name'=>'private');
  if ($cameralife->Database->SelectOne('photos','COUNT(*)','status=3'))
    $sections[] = array('name'=>'New Uploaded Pics','page_name'=>'upload');
  $sections[] = array('name'=>'Update Database','page_name'=>'update');

  $cameralife->Theme->MultiSection($sections);

  if ($_GET['page'] == 'flagged')
    $target_status = 1;
  else if ($_GET['page'] == 'private')
    $target_status = 2;
  else if ($_GET['page'] == 'upload')
    $target_status = 3;

  if ($_GET['page'] !== 'update') // Show stuff
  {
    if ($_GET['page'] == 'flagged')
      echo "<p class='administrative'>Photos that have been flagged will show up here. If you 'erase' a photo. It will be moved moved to <b>".$cameralife->preferences['core']['deleted_dir']."</b> <a href='customize.php'>(change)</a>. You may send flagged photos to the private photo section.</p>";
    else if ($_GET['page'] == 'private')
      echo '<p class="administrative">Photos that have been marked private will show here.</p>';
    else if ($_GET['page'] == 'upload')
      echo '<p class="administrative">Photos that have been uploaded by users will show here.</p>';

    echo '<p>Change All: <select name="status" onchange="changeall()" id="status">';
    echo '<option value="0" '.($target_status==0?'selected':'').'>Public</option>';
    echo '<option value="1" '.($target_status==1?'selected':'').'>Flagged</option>';
    echo '<option value="2" '.($target_status==2?'selected':'').'>Private</option>';
    echo '<option value="3" '.($target_status==3?'selected':'').'>New Upload</option>';
    echo '<option value="4" '.($target_status==4?'selected':'').'>Erased</option>';
    echo '</select></p>';

    $search = new Search('');
//TODO wow...
    // You know code is a hack when you use a SQL injection attack against yourself.
    $search->mySearchPhotoCondition = "status=$target_status OR 0";
    $photos = $search->GetPhotos();
    $icons = array();

    foreach($photos as $photo)
    {
        $icon = $photo->GetIcon();
        $icon['image'] = '../' . $icon['image'];
        $icon['href'] = '../' . $icon['href'];
        $icon['name'] = '<select name="'.$photo->Get('id').'">'.
                        '<option value="0" '.($target_status==0?'selected':'').'>Public</option>'.
                        '<option value="1" '.($target_status==1?'selected':'').'>Flagged</option>'.
                        '<option value="2" '.($target_status==2?'selected':'').'>Private</option>'.
                        '<option value="3" '.($target_status==3?'selected':'').'>New Upload</option>'.
                        '<option value="4" '.($target_status==4?'selected':'').'>Erased</option></select><br>'.
                        $icon['name'];
        $icons[] = $icon;
    }

    $cameralife->Theme->Grid($icons);
    $total = $cameralife->Database->SelectOne('photos','COUNT(*)',"status=$target_status");
?>
<p>
  <input type=submit value="Commit Changes">
  <a href="<?= $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'] ?>">(Undo changes)</a>
</p>
<?php
    $cameralife->Theme->PageSelector($_GET['start'],$total,12,"page=".$_GET["page"]);
  }
  else // Update DB
  {
    echo "</table>\n";
    echo "<p>Updating the database to reflect any changes to the photos directory...</p>\n<ol>\n";
    flush();

    $output = Folder::Update();
    foreach($output as $line)
      echo "<li>$line</li>\n";

    echo "</ol>\n<p>Updating complete :-) Now you can:<ul>\n";
    echo "<li><a href=\"../search.php?q=unnamed\">Name your new files</a></li>\n";
    echo "<li><a href='thumbnails.php'>Optimize thumbnails</a></li>\n";
    echo "</ul>\n";
  }
?>
  </table>
  </form>
</body>
</html>

