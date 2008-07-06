<?php
  # Browses a give path for photos
  # Magic vars GET:start, GET/POST:sort are handled in search.class.php
  
  $features=array('database','theme','security', 'photostore');
  require "main.inc";

  $folder = new Folder($_GET['path'], $true);
  $counts = $folder->GetCounts();

  if ($_GET['page'] == 'folders' || !$counts['photos'])
    $results = $folder->GetChildren();
  else
  {
    $_GET['page'] = 'photos';
    $results = $folder->GetPhotos();
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
</head>
<body>
<form method="post"> 
<?php
$menu = array();
  $menu[] = $cameralife->GetIcon('small');
  if ($folder->Path())
    $menu[] = array('name'=>'Search for '.$folder->Basename(),
                    'href'=>'search.php&#63;q='.$folder->Basename(),
                    'image'=>'small-search');

  foreach($folder->GetAncestors() as $ancestor)
    $menu[] = $ancestor->GetIcon('small');

  if ($cameralife->Security->Authorize('admin_file'))
    $menu[] = array('name'=>'Upload photo here',
                    'href'=>$cameralife->base_url.'/upload.php&#63;path='.$folder->Path(),
                    'image'=>'small-main');
  
  $folder_name = $folder->Basename()
    or $folder_name = '(Top Level)';
  $cameralife->Theme->TitleBar($folder_name,
                               'folder',
                               FALSE,
                               $menu);
  if ($counts['photos'] > 0)
    $sections[] = array('name'=>"Contains ".$counts['photos']." photos",
                        'page_name'=>'photos',
                        'small-photo');
  if ($counts['folders'] > 0)
    $sections[] = array('name'=>"Contains ".$counts['folders']." folders",
                        'page_name'=>'folders',
                        'small-folder');

  if (count($sections) > 0)
    $cameralife->Theme->MultiSection($sections, array('path'=>$_GET['path']));
  else
  {
    $cameralife->Error('This folder does not exist.');
  }

  $cameralife->Theme->Grid($results);

  if ($sort == 'rand()') $start = -1;

  if ($_GET['page'] == "photos")
    $cameralife->Theme->PageSelector($_GET['start'],$counts['photos'],12,"page=photos&amp;path=".$_GET["path"]);
  else // ($_GET['page'] == "folders")
    $cameralife->Theme->PageSelector ($_GET['start'],$counts['folders'],12,"page=folders&amp;path=".$_GET["path"]);

?>

<p>
  Sort by <select name="sort">
<?php
    $options = Search::SortOptions();
    foreach($options as $option)
      echo "    <option value=\"".$option[0]."\">".$option[1]."</option>\n";
?>
  </select>
  <input type=submit value="Sort">
</p>

</form>
</body>
</html>
