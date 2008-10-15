<?php
  # $cameralife and $search are set by ShowPage()

  $cameralife->GetFeature('security');
  $cameralife->GetFeature('photostore');

  $start = $_GET['start'] 
    or $start = 0;

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

  $header = '  <link rel="alternate" type="application/rss+xml" title="RSS feed of'.$_GET['q'].'" href="'.$camerasife->base_url.'/rss.php&#63;q='.$_GET['q'].'" />';
  $cameralife->Theme->Header('Site search', 'main', $header);
?>
<div id="sidebar">
<form method="GET"> 
  <div class="item">
    <a href="?q=unnamed">Search for unnamed photos</a>
  </div>
<?php

  if($_GET['page'] == "p" && $cameralife->Security->Authorize('admin_albums'))
    echo '<div class="item"><a href="topic.php&#63;edit=true&amp;term='.urlencode($_GET['q']).'">Create an Album of these photos</a></div>';

echo '</div><div id="text">';
  if ($_GET['albumhelp'])
  {
?>
    <?php $cameralife->Theme->Section('How to use Albums') ?>

      <?php $cameralife->Theme->Image('small-album', array('align'=>'middle')) ?> An Album is a collection of photos with a common term in their description. <br>
      <?php $cameralife->Theme->Image('small-topic', array('align'=>'middle')) ?> A Topic is a logical collection of Albums. Ex: People, Places, Events<br>
      <?php $cameralife->Theme->Image('small-search', array('align'=>'middle')) ?> To create an album, perform a search, then choose "Create an album from these photos" on the toolbar.<br>
      <p>Note: In the future, you can simply perform a search, without pulling up these instructions.
      <?php if (file_exists('setup/albums.html')) echo "<a href=\"setup/albums.html\">Click here for more information</a>"; ?></p>
<?php
    }

  if($_GET['albumhelp']) exit(0);

  if ($counts['albums'] > 0)
    $sections[] = array('name'=>$counts['albums'].' albums of '.$_GET['q'],
                        'page_name'=>'a',
                        'image'=>'small-topic');
  if ($counts['folders'] > 0)
    $sections[] = array('name'=>$counts['folders'].' folders of '.$_GET['q'],
                        'page_name'=>'f',
                        'image'=>'small-topic');
  if ($counts['photos'] > 0)
    $sections[] = array('name'=>$counts['photos'].' photos of '.$_GET['q'],
                        'page_name'=>'p',
                        'image'=>'small-topic');

  if (count($sections) > 0)
    $cameralife->Theme->MultiSection($sections, array('q'=>$_GET['q']));
  else
    $cameralife->Theme->Section('Sorry, no results');

  $cameralife->Theme->Grid($results);

  if ($_GET['page'] == "a")
    $cameralife->Theme->PageSelector($start,$counts['albums'],12,"page=a&amp;q=".$_GET["q"]);
  else if ($_GET['page'] == "f")
    $cameralife->Theme->PageSelector($start,$counts['folders'],12,"page=f&amp;q=".$_GET["q"]);
  else
    $cameralife->Theme->PageSelector($start,$counts['photos'],12,"page=p&amp;q=".$_GET["q"]);
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
<?php
  $cameralife->Theme->Footer();
?>
