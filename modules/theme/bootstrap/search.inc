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
    <h4>Create a Featured Tag</h4>
    <i class="fa fa-fw fa-photo fa-2x"></i> Photos have descriptions and tags associated with them<br>
    <i class="fa fa-fw fa-tags fa-2x"></i> Tags are grouped into topics<br>
    <i class="fa fa-fw fa-search fa-2x"></i> To feature a tag, perform a search and then choose "Feature this tag".<br>
    <?php
    exit(0);
}

    echo '<h1>';
    echo "Search for <code>" . htmlentities($search->query) . "</code></h1>";
    echo '<ul class="nav nav-pills">' . PHP_EOL;
    echo '<li class="';
if ($_GET["page"] == 'photos') {
    echo 'active ';
}
if (!$numPhotos) {
    echo 'disabled ';
}
    $href = "&#63;q=" . urlencode($search->query) . "&amp;page=photos";
    echo "\"><a href=\"$href\">$numPhotos photos</a></li>" . PHP_EOL;
    echo '<li class="';
if ($_GET["page"] == 'albums') {
    echo 'active ';
}
if (!$numAlbums) {
    echo 'disabled ';
}
    $href = "&#63;q=" . urlencode($search->query) . "&amp;page=albums";
    echo "\"><a href=\"$href\">$numAlbums albums</a></li>" . PHP_EOL;
    echo '<li class="';
if ($_GET["page"] == 'folders') {
    echo 'active ';
}
if (!$numFolders) {
    echo 'disabled ';
}
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
    