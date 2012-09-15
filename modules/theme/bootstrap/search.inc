<?php
/**
 * Implements various search options
 * The following variables are set by ShowPage()
 * <ul>
 * <li>$cameralife</li>
 * <li>$search </li>
 * </ul>
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */
  
// $cameralife and $search are set by ShowPage()

$cameralife->GetFeature('security');
$cameralife->GetFeature('photostore');

$start = $_GET['start'] + 0;
$search->SetPage($start);
$counts = $search->GetCounts();
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

$header = '  <link rel="alternate" type="application/rss+xml" title="RSS feed of'.htmlentities($search->GetQuery()).'" href="'.$camerasife->base_url.'/rss.php?q='.urlencode($search->GetQuery()).'" />';

$sidebar = <<<HTML
    <ul class="nav nav-list">
        <li class="nav-header">Tools</li>
        <li><a href="?q=unnamed">Search for unnamed photos</a></li>
HTML;
if($_GET['page'] == "p" && $cameralife->Security->Authorize('admin_albums')) {
    $queryHTML = urlencode($search->GetQuery());
    $sidebar .= <<<HTMLx
        <li><a href="topic.php&#63;edit=true&amp;term=$queryHTML">Create an Album of these photos</a></li>
HTMLx;
}
$sidebar .= <<<HTML
    </ul>
HTML;

$cameralife->Theme->Header('Site search', $sidebar, $header);

if ($_GET['albumhelp']) {
?>
<div class="alert alert-info alert-block">
  <button type="button" class="close" data-dismiss="alert">×</button>
  <h4>How to use Albums</h4>
    <?php $cameralife->Theme->Image('small-album', array('align'=>'middle')) ?> 
    An Album is a collection of photos with a common term in their description. <br>
    <?php $cameralife->Theme->Image('small-topic', array('align'=>'middle')) ?> 
    A Topic is a logical collection of Albums. Ex: People, Places, Events<br>
    <?php $cameralife->Theme->Image('small-search', array('align'=>'middle')) ?> 
    To create an album, perform a search, then choose "Create an album from these photos" on the toolbar.<br>
    <p>Note: In the future, you can simply perform a search, without pulling up these instructions.
    <a href="https://github.com/fulldecent/cameralife/wiki/Albums">Click here for more information</a></p>
<?php
  exit(0);
}

echo "<h1>";
$cameralife->Theme->Image('search');
echo "Search for ".htmlentities($search->GetQuery())."</h1>";
echo '<ul class="nav nav-pills">'.PHP_EOL;
foreach ($counts as $type=>$num) {
    echo "<li";
    $class = array();
    if ($_GET["page"] == $type[0])
        $class[] = "active";
    if ($num == 0)
        $class[] = "disabled";
    if (count($class)) 
        echo " class=\"".implode(" ",$class)."\"";
    if ($num == 1 && substr($type,-1)=='s')
        $type = substr($type, 0 -1);
    $href = "&#63;q=".urlencode($search->GetQuery())."&amp;page=".$type[0];
    if ($num == 0)
        $href = "#";
    echo "><a href=\"$href\">$num $type</a></li>".PHP_EOL;
}
echo "</ul>".PHP_EOL;

  $cameralife->Theme->Grid($results);

  echo "<form>";
  echo "<input type=hidden name=q value=\"".htmlentities($search->GetQuery())."\" />";
  echo "<input type=hidden name=page value=\"".htmlentities($_GET['page'])."\" />";
  $icon = $search->GetIcon();
  $href = AddParam($icon['href'], "page", $_GET['page']);
  if ($_GET['page'] == "a")
    $cameralife->Theme->PageSelector($start,$counts['albums'],12,$href);
  else if ($_GET['page'] == "f")
    $cameralife->Theme->PageSelector($start,$counts['folders'],12,$href);
  else
    $cameralife->Theme->PageSelector($start,$counts['photos'],12,$href);
    
  echo "<select name=\"sort\">\n";
  foreach ($search->SortOptions() as $sortOption) {
    list($id, $desc, $selected) = $sortOption;
    echo "<option value=\"$id\" $selected>$desc</option>\n";
  }  
  echo "</select>\n";
  echo "<input class=\"btn\" type=submit value=\"Sort\">\n";
  echo "</form>";    

  $cameralife->Theme->Footer();
?>
