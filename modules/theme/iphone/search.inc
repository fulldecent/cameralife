<?php
/**Implements various search options
*The following variables are set by ShowPage()
*<ul>
*<li>$cameralife</li>
*<li>$search </li>
*</ul>
*@link http://fdcl.sourceforge.net
    *@version 2.6.3
    *@author Will Entriken <cameralife@phor.net>
    *@copyright Copyright (c) 2001-2009 Will Entriken
    *@access public
    */
/**
*/
  # $cameralife and $search are set by ShowPage()

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');

  $start = $_GET['start'] + 0;
  $counts = $search->GetCounts();

  $search->SetPage($start);
  if (($_GET['page'] == 'a') || (!isset($_GET['page']) && $counts['albums'] >0))
  {
    $_GET['page'] = 'a';
    $results = $search->GetAlbums();
  } 
  elseif (($_GET['page'] == 'f') || (!isset($_GET['page']) && $counts['folders']>0))
  {
    $_GET['page'] = 'f';
    $results = $search->GetFolders();
  } 
  else 
  {
    $_GET['page'] = 'p';
    $results = $search->GetPhotos();
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
<h1><?= htmlentities($search->GetQuery()) ?></h1>
<form method="GET"> 
  
<?php 
  if ($counts['albums'] > 0)
    $sections[] = array('name'=>$counts['albums'].' albums of '.htmlentities($search->GetQuery()),
                        'page_name'=>'a',
                        'image'=>'small-topic');
  if ($counts['folders'] > 0)
    $sections[] = array('name'=>$counts['folders'].' folders of '.htmlentities($search->GetQuery()),
                        'page_name'=>'f',
                        'image'=>'small-topic');
  if ($counts['photos'] > 0)
    $sections[] = array('name'=>$counts['photos'].' photos of '.htmlentities($search->GetQuery()),
                        'page_name'=>'p',
                        'image'=>'small-topic');

  if (count($sections) > 0)
    $cameralife->Theme->MultiSection($sections, array('q'=>htmlentities($search->GetQuery())));
  else
    $cameralife->Theme->Section('Sorry, no results');

  $cameralife->Theme->Grid($results);

  if ($_GET['page'] == "a")
    $cameralife->Theme->PageSelector($start,$counts['albums'],12,"page=a&amp;q=".urlencode($search->GetQuery()));
  else if ($_GET['page'] == "f")
    $cameralife->Theme->PageSelector($start,$counts['folders'],12,"page=f&amp;q=".urlencode($search->GetQuery()));
  else
    $cameralife->Theme->PageSelector($start,$counts['photos'],12,"page=p&amp;q=".urlencode($search->GetQuery()));
/*
  $cameralife->Theme->SortSelector('id'=>'Oldest First','id desc'=>'Newest First',
                                   'description'=>'A-Z', 'description desc'=>'Z-A',
                                   'comments'=>'Most popular', 'comments desc'=>'Least Popular',
                                   'rand()'=>'Randomly');
*/
?>

<p>
  Sort by <select name="sort">
    <option <?php if ($sort=="id") echo "selected" ?> value="id">Oldest First</option>
    <option <?php if ($sort=="id desc") echo "selected" ?> value="id desc">Newest First</option>
    <option <?php if ($sort=="description") echo "selected" ?> value="description">Alphabetically</option>
    <option <?php if ($sort=="description desc") echo "selected" ?> value="description desc">Alphabetically (backwards)</option>
    <option <?php if ($sort=="comment desc") echo "selected" ?> value="comment desc">Most popular first</option>
    <option <?php if ($sort=="comment") echo "selected" ?> value="comment">Least popular first</option>
    <option <?php if ($sort=="rand()") echo "selected" ?> value="rand()">Randomly</option>
  </select>
  <input type=submit value="Sort">
</p>

</form>
</html>
