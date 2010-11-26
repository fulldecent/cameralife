<?php
/**
 * Establishes path of the photos folder
 *
 *<b>Note</b>  $cameralife and $folder are set by ShowPage()
 *
 *Magic vars GET:start, GET/POST:sort are handled in search.class.php
 *@link http://fdcl.sourceforge.net/
 *@version 2.6.3b6
 *@author Will Entriken <cameralife@phor.net>
 *@access public
 *@copyright Copyright (c) 2001-2009 Will Entriken
 */

# $cameralife and $folder are set by ShowPage()
# Browses a give path for photos
# Magic vars GET:start, GET/POST:sort are handled in search.class.php
  
$counts = $folder->GetCounts();

if (isset($_GET['start'])&&is_numeric($_GET['start']))
  $folder->myLimit = "LIMIT {$_GET['start']}, 20";
else 
  $folder->myLimit = "LIMIT 0, 20";

if ($_GET['page'] == 'folders' || !$counts['photos']) {
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

  $cameralife->Theme->Header($folder_name, 'folder');

?>
    <div id="sidebar">
      <h1>Folder View</h1> 
      <ul>
<?php
  foreach($folder->GetAncestors() as $ancestor) {
    $icon = $ancestor->GetIcon('small');
    echo "        <li><a href=\"{$icon['href']}\">{$icon['name']}</a></li>\n";
  }
  echo "        <li>$folder_name</li>\n";
?>
      </ul>
    </div>
<div id="text">
<?php

  if ($counts['photos'] > 0)
    $sections[] = array('name'=>$counts['photos']." photos", 'page_name'=>'photos');
  if ($counts['folders'] > 0)
    $sections[] = array('name'=>$counts['folders']." folders", 'page_name'=>'folders');

  if (count($sections) >= 2) {
    $icon = $folder->GetIcon();
    echo "This folder contains ";
    $cameralife->Theme->MultiSection($sections, $icon['href']);
  }
  elseif (count($sections == 1))
    echo "This folder contains ".strtolower($sections[0]['name']);
  else
    $cameralife->Error('This folder does not exist.');

  $cameralife->Theme->Grid($results);

  if ($sort == 'rand()') $start = -1;

  $icon = $folder->GetIcon();
  if ($_GET['page'] == "photos")
    $cameralife->Theme->PageSelector($_GET['start'],$counts['photos'],20,$icon['href']);
  else // ($_GET['page'] == "folders")
    $cameralife->Theme->PageSelector($_GET['start'],$counts['folders'],20,$icon['href']);

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
