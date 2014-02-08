<?php
/**
 * Folder view 
 * @author Will Entriken <cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2001-2009 Will Entriken
 */

# $cameralife and $folder are set by ShowPage()
# Browses a give path for photos
# Magic vars GET:start, GET/POST:sort are handled in search.class.php
  
$counts = $folder->GetCounts();

if (isset($_GET['start']) && is_numeric($_GET['start']))
  $folder->SetPage($_GET['start'], 25);
else 
  $folder->SetPage(0, 25);

if (isset($_GET['page']) && $_GET['page'] == 'folders' || !$counts['photos']) {
  $_GET['page'] = 'folders';
  $results = $folder->GetChildren();
} else {
  $_GET['page'] = 'photos';
  $results = $folder->GetPhotos();
}

$menu = array();
  if ($folder->Path())
    $menu[] = array('name'=>'Search for '.$folder->Basename(),
                    'href'=>'search.php&#63;q='.$folder->Basename(),
                    'image'=>'small-search');

  if ($cameralife->Security->Authorize('admin_file'))
    $menu[] = array('name'=>'Upload photo here',
                    'href'=>$cameralife->base_url.'/upload.php&#63;path='.$folder->Path(),
                    'image'=>'small-main');
  
  $folder_name = $folder->Basename()
    or $folder_name = '(Top Level)';

  $cameralife->Theme->Header($folder_name);
?>

<ul class="breadcrumb">
<?php
  foreach($folder->GetAncestors() as $ancestor) {
    $icon = $ancestor->GetIcon('small');
    echo "  <li><a href=\"{$icon['href']}\"><img alt=\"folder\" src=\"".$cameralife->IconURL('small-folder')."\"> {$icon['name']}</a></li>";
  }
  echo "        <li><img alt=\"folder\" src=\"".$cameralife->IconURL('small-folder')."\"> $folder_name</li>\n";
?>
</ul>

<?php

  if ($counts['photos'] > 0)
    $sections[] = array('name'=>$counts['photos']." photos", 'page_name'=>'photos');
  if ($counts['folders'] > 0)
    $sections[] = array('name'=>$counts['folders']." folders", 'page_name'=>'folders');

  if (count($sections) >= 2) {
    $icon = $folder->GetIcon();
    $cameralife->Theme->MultiSection($sections, $icon['href']);
  }
  elseif (count($sections == 1))
    echo "This folder contains ".strtolower($sections[0]['name']);
  else
    $cameralife->Error('This folder does not exist.');

  $cameralife->Theme->Grid($results);

  $icon = $folder->GetIcon();
  $start = isset($_GET['start']) ? $_GET['start'] : 0;
  if ($_GET['page'] == "photos")
    $cameralife->Theme->PageSelector($start,$counts['photos'],25,$icon['href']);
  else // ($_GET['page'] == "folders")
    $cameralife->Theme->PageSelector($start,$counts['folders'],25,$icon['href']);

?>

<form class="form-inline">
  <div class="form-group">
    Sort by
    <select name="sort" class="form-control input-sm" style="width:200px">
<?php
    $options = $folder->SortOptions();    
    foreach($options as $option) {
      if (isset($option[2]))
        echo "    <option ".$option[2]." value=\"".$option[0]."\">".$option[1]."</option>\n";
      else
        echo "    <option value=\"".$option[0]."\">".$option[1]."</option>\n";
    }
?>
    </select>
    <input type=submit value="Sort" class="btn btn-default btn-sm">
  </div>
</form>

<?php
  $cameralife->Theme->Footer();
?>
