<?php
/**
 * Implements various search options
 * The following variables are set by showPage()
 * <ul>
 * <li>$cameralife</li>
 * <li>$search</li>
 * </ul>
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */

// $cameralife and $search are set by showPage()

$cameralife->getFeature('security');
$cameralife->getFeature('fileStore');


$start = isset($_GET['start']) ? $_GET['start'] + 0 : 0;
$search->setPage($start);
$counts = $search->getCounts();
if ((isset($_GET['page']) && $_GET['page'] == 'a') || (!isset($_GET['page']) && $counts['albums'] > 0)) {
    $_GET['page'] = 'a';
    $results = $search->getAlbums();
} elseif ((isset($_GET['page']) && $_GET['page'] == 'f') || (!isset($_GET['page']) && $counts['folders'] > 0)) {
    $_GET['page'] = 'f';
    $results = $search->getFolders();
} else {
    $_GET['page'] = 'p';
    $results = $search->getPhotos();
}

$header = '  <link rel="alternate" type="application/rss+xml" title="RSS feed of' . htmlentities(
        $search->getQuery()
    ) . '" href="' . $cameralife->baseURL . '/rss.php?q=' . urlencode($search->getQuery()) . '" />';

$sidebar = <<<HTML
    <ul class="nav nav-list">
        <li class="nav-header">Tools</li>
        <li><a href="?q=unnamed">Search for unnamed photos</a></li>
HTML;
if ($_GET['page'] == "p" && $cameralife->security->Authorize('admin_albums')) {
    $queryHTML = urlencode($search->getQuery());
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
    echo "Search for " . htmlentities($search->getQuery()) . "</h1>";
    echo '<ul class="nav nav-pills">' . PHP_EOL;
    foreach ($counts as $type => $num) {
        echo "<li";
        $class = array();
        if ($_GET["page"] == $type[0]) {
            $class[] = "active";
        }
        if ($num == 0) {
            $class[] = "disabled";
        }
        if (count($class)) {
            echo " class=\"" . implode(" ", $class) . "\"";
        }
        if ($num == 1 && substr($type, -1) == 's') {
            $type = substr($type, 0, -1);
        }
        $href = "&#63;q=" . urlencode($search->getQuery()) . "&amp;page=" . $type[0];
        if ($num == 0) {
            $href = "";
        }
        echo "><a href=\"$href\">$num $type</a></li>" . PHP_EOL;
    }
    echo "</ul>" . PHP_EOL;

    $cameralife->theme->grid($results);

    echo "<form>";
    echo "<input type=hidden name=q value=\"" . htmlentities($search->getQuery()) . "\" />";
    echo "<input type=hidden name=page value=\"" . htmlentities($_GET['page']) . "\" />";
    $openGraph = $search->getOpenGraph();
    $href = AddParam($openGraph['og:url'], "page", $_GET['page']);
    if ($_GET['page'] == "a") {
        $cameralife->theme->pageSelector($start, $counts['albums'], 12, $href);
    } else {
        if ($_GET['page'] == "f") {
            $cameralife->theme->pageSelector($start, $counts['folders'], 12, $href);
        } else {
            $cameralife->theme->pageSelector($start, $counts['photos'], 12, $href);
        }
    }

    echo "<select name=\"sort\">\n";
    foreach ($search->sortOptions() as $sortOption) {
        list($id, $desc, $selected) = $sortOption;
        echo "<option value=\"$id\" $selected>$desc</option>\n";
    }
    echo "</select>\n";
    echo "<input class=\"btn\" type=submit value=\"Sort\">\n";
    echo "</form>";

    $cameralife->theme->footer();
    ?>
