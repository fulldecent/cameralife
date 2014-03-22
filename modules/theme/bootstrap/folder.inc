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
$openGraph = $folder->GetOpenGraph();
$start = isset($_GET['start']) ? $_GET['start'] : 0;

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

$headers = '';
if ($folder->GetPrevious()) {
  $headers = "<link rel=\"prev\" href=\"".$folder->GetPrevious()."\" />\n";
}
if ($folder->GetNext()) {
  $headers = "<link rel=\"next\" href=\"".$folder->GetNext()."\" />\n";
}

$cameralife->Theme->Header($folder, NULL, $headers);
?>

<ul class="breadcrumb">
<?php
  foreach($folder->GetAncestors() as $ancestor) {
    $ancestorOpenGraph = $ancestor->GetOpenGraph();
    echo '  <li><a href="'.htmlspecialchars($ancestorOpenGraph['og:url']).'"><img alt="folder" src="'.htmlspecialchars($cameralife->IconURL('small-folder')).'"> '.htmlentities($ancestorOpenGraph['og:title']).'</a></li>';
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
    $cameralife->Theme->MultiSection($sections, $openGraph['og:url']);
  }
  elseif (count($sections == 1))
    echo "This folder contains ".strtolower($sections[0]['name']);
  else
    $cameralife->Error('This folder does not exist.');

  $cameralife->Theme->Grid($results);

  if ($_GET['page'] == "photos")
    $cameralife->Theme->PageSelector($start,$counts['photos'],25,$openGraph['og:url']);
  else // ($_GET['page'] == "folders")
    $cameralife->Theme->PageSelector($start,$counts['folders'],25,$openGraph['og:url']);

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
