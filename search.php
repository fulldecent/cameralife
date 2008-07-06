<?php
  $features=array('database','theme','security', 'photostore');
  require "main.inc";

  $start = $_GET['start'] 
    or $start = 0;

  /* Bonus code to log searches
  $log_handle = fopen ("search.log", "a");
  fwrite($log_handle, $_GET["q"]."\n");
  fclose ($log_handle);
  */

  $search = new Search($_GET['q']);
  $counts = $search->GetCounts();

  #Be intelligent here...
  if (!$counts['folders'] && $counts['albums'] == 1)
  {
    $count_term = $cameralife->Database->SelectOne('albums','COUNT(*)',"term LIKE '".$_GET['q']."'");
    if ($count_term == 1)
    {
      $albumid = $cameralife->Database->SelectOne('albums','id',"term LIKE '".$_GET['q']."'");
      header('Location: '.$cameralife->base_url.'/album.php?id='.$albumid);
      echo 'redirecting...';
      exit(0);
    }
  }

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
  <link rel="alternate" type="application/rss+xml" title="RSS feed of <?= $_GET['q'] ?>" href="rss.php&#63;q=<?= $_GET['q'] ?>"/>
</head>
<body>
<form method="GET"> 
  
<?php
  $menu = array();
  $menu[] = array('name'=>$cameralife->GetPref('siteabbr'),
                  'href'=>'index.php',
                  'image'=>'small-main');
  $menu[] = array('name'=>'Search for everything',
                  'href'=>'search.php',
                  'image'=>'small-search');
  $menu[] = array('name'=>'Search unnamed photos',
                  'href'=>'search.php?q=unnamed',
                  'image'=>'small-search');

  if($_GET['page'] == "p" && $cameralife->Security->Authorize('admin_albums'))
          $menu[] = array('name'=>'Create an Album of these photos',
                          'href'=>'topic.php&#63;edit=true&amp;term='.urlencode($_GET['q']),
                          'image'=>'small-album');

  $cameralife->Theme->TitleBar('Site search', 'main', FALSE, $menu);

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

    $cameralife->Theme->Section('Search for')
?>
  <input type="text" name="q" value="<?= stripslashes($_GET['q']) ?>">
  <input type="hidden" name="page" value="<?= $_GET['page'] ?>">
  <input type="image" src="<?= $cameralife->Theme->ImageURL('search') ?>" value="search">

<?php 
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
