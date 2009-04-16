<?php
/**
  * Establishes path of the photos folder
  *
  *<b>Note</b>  $cameralife and $folder are set by ShowPage()
  *
  Magic vars GET:start, GET/POST:sort are handled in search.class.php
  *@link http://fdcl.sourceforge.net/
  *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@access public
  *@copyright Copyright (c) 2001-2009 Will Entriken
  */
  /**
  */
  # $cameralife and $folder are set by ShowPage()
  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');

  # Browses a give path for photos
  # Magic vars GET:start, GET/POST:sort are handled in search.class.php
  
  $counts = $folder->GetCounts();

  if ($_GET['page'] == 'folders' || !$counts['photos'])
    $results = $folder->GetChildren();
  else
  {
    $_GET['page'] = 'photos';
    $results = $folder->GetPhotos();
  }


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

  $cameralife->Theme->Header($folder_name, 'folder');
?>
<div id="text">
<?php

  if ($counts['photos'] > 0)
    $sections[] = array('name'=>"Contains ".$counts['photos']." photos",
                        'page_name'=>'photos',
                        'small-photo');
  if ($counts['folders'] > 0)
    $sections[] = array('name'=>"Contains ".$counts['folders']." folders",
                        'page_name'=>'folders',
                        'small-folder');

  if (count($sections) > 0)
    $cameralife->Theme->MultiSection($sections, array('path'=>htmlentities($folder->Path())));
  else
  {
    $cameralife->Error('This folder does not exist.');
  }

  $cameralife->Theme->Grid($results);

  if ($sort == 'rand()') $start = -1;

  if ($_GET['page'] == "photos")
    $cameralife->Theme->PageSelector($_GET['start'],$counts['photos'],12,"page=photos&amp;path=".urlencode($folder->Path()));
  else // ($_GET['page'] == "folders")
    $cameralife->Theme->PageSelector ($_GET['start'],$counts['folders'],12,"page=folders&amp;path=".urlencode($folder->Path()));

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
