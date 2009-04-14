<?php
  # $cameralife and $folder are set by ShowPage()
  /**
  * Establishing path of the photos folder.
    *<b>Note</b>
    *<ul>
  *<li><$cameralife and $folder are set by ShowPage()</li>  
  *<li>Magic vars GET:start, GET/POST:sort are handled in search.class.php</li>
  *</ul>
  *@link http://fdcl.sourceforge.net/
  *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@access public
  *@copyright © 2001-2009 Will Entriken
  */
  /**
  */

  
  #$cameralife and $folder are set by ShowPage()
  
  #Magic vars GET:start, GET/POST:sort are handled in search.class.php
  
  
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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?= $cameralife->GetPref('sitename') ?></title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
  <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=yes;" />

</head>
<body>
<h1><?= $folder->path ?></h1>
<form method="post"> 
<?php
  
  $folder_name = $folder->Basename()
    or $folder_name = '(Top Level)';
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
