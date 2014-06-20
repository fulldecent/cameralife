<?php
namespace CameraLife;
/**
 * Implements various search options
 * The following variables are set by showPage()
 * <ul>
 * <li>$cameralife</li>
 * <li>$search</li>
 * </ul>
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */

// $cameralife and $search are set by showPage()

$cameralife->getFeature('security');
$cameralife->getFeature('fileStore');

$start = isset($_GET['start']) ? $_GET['start'] + 0 : 0;
$search->setPage($start);
$numPhotos = $search->getPhotoCount();
$numAlbums = $search->getAlbumCount();
$numFolders = $search->getFolderCount();
if ((isset($_GET['page']) && $_GET['page'] == 'albums') || (!isset($_GET['page']) && $numAlbums > 0)) {
    $_GET['page'] = 'albums';
    $results = $search->getAlbums();
} elseif ((isset($_GET['page']) && $_GET['page'] == 'folders') || (!isset($_GET['page']) && $numFolders > 0)) {
    $_GET['page'] = 'folders';
    $results = $search->getFolders();
} else {
    $_GET['page'] = 'photos';
    $results = $search->getPhotos();
}

$header = '  <link rel="alternate" type="application/rss+xml" title="RSS feed of' . htmlentities(
        $search->query
    ) . '" href="' . $cameralife->baseURL . '/rss.php?q=' . urlencode($search->query) . '" />';

$sidebar = <<<HTML
    <ul class="nav nav-list">
        <li class="nav-header">Tools</li>
        <li><a href="?q=unnamed">Search for unnamed photos</a></li>
HTML;
if ($_GET['page'] == "p" && $cameralife->security->Authorize('admin_albums')) {
    $queryHTML = urlencode($search->query);
    $sidebar .= <<<HTMLx
        <li><a href="topic.php&#63;edit=true&amp;term=$queryHTML">Create an Album of these photos</a></li>
HTMLx;
}
$sidebar .= <<<HTML
    </ul>
HTML;

$cameralife->theme->header($search, $sidebar, $header);

if (isset($_GET['albumhelp']) && $_GET['albumhelp']) {
?>
<div class="alert alert-info alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <h4>How to use Albums</h4>
    <?php $cameralife->theme->Image('small-album', array('align' => 'middle')) ?>
    An Album is a collection of photos with a common term in their description. <br>
    <?php $cameralife->theme->Image('small-topic', array('align' => 'middle')) ?>
    A Topic is a logical collection of Albums. Ex: People, Places, Events<br>
    <?php $cameralife->theme->Image('small-search', array('align' => 'middle')) ?>
    To create an album, perform a search, then choose "Create an album from these photos" on the toolbar.<br>

    <p>Note: In the future, you can simply perform a search, without pulling up these instructions.
        <a href="https://github.com/fulldecent/cameralife/wiki/Albums">Click here for more information</a></p>
    <?php
    exit(0);
    }

    echo "<h1>";
    $cameralife->theme->Image('search');
    echo "Search for " . htmlentities($search->query) . "</h1>";
    echo '<ul class="nav nav-pills">' . PHP_EOL;
    echo '<li class="';
    if ($_GET["page"] == 'photos') echo 'active ';
    if (!$numPhotos) echo 'disabled ';
    $href = "&#63;q=" . urlencode($search->query) . "&amp;page=photos";
    echo "\"><a href=\"$href\">$numPhotos photos</a></li>" . PHP_EOL;
    echo '<li class="';
    if ($_GET["page"] == 'albums') echo 'active ';
    if (!$numAlbums) echo 'disabled ';
    $href = "&#63;q=" . urlencode($search->query) . "&amp;page=albums";
    echo "\"><a href=\"$href\">$numAlbums albums</a></li>" . PHP_EOL;
    echo '<li class="';
    if ($_GET["page"] == 'folders') echo 'active ';
    if (!$numFolders) echo 'disabled ';
    $href = "&#63;q=" . urlencode($search->query) . "&amp;page=folders";
    echo "\"><a href=\"$href\">$numFolders folders</a></li>" . PHP_EOL;
    echo "</ul>" . PHP_EOL;

    $cameralife->theme->grid($results);
    echo "<form>";
    echo "<input type=hidden name=q value=\"" . htmlentities($search->query) . "\" />";
    echo "<input type=hidden name=page value=\"" . htmlentities($_GET['page']) . "\" />";
    $openGraph = $search->getOpenGraph();
    $href = $cameralife->theme->addParam($openGraph['og:url'], "page", $_GET['page']);
    if ($_GET['page'] == "a") {
        $cameralife->theme->pageSelector($start, $numAlbums, 12, $href);
    } else {
        if ($_GET['page'] == "f") {
            $cameralife->theme->pageSelector($start, $numFolders, 12, $href);
        } else {
            $cameralife->theme->pageSelector($start, $numPhotos, 12, $href);
        }
    }

    echo "<select class=\"form-control\" style=\"width:200px; display:inline\" name=\"sort\">\n";
    foreach ($search->sortOptions() as $sortOption) {
        list($id, $desc, $selected) = $sortOption;
        echo "<option value=\"$id\" $selected>$desc</option>\n";
    }
    echo "</select>\n";
    echo "<input class=\"btn\" type=submit value=\"Sort\">\n";
    echo "</form>";

    $cameralife->theme->footer();
    ?>
