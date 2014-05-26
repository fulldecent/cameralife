<?php
namespace CameraLife;
/**
 * Folder view
 * @author William Entriken <cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2001-2009 William Entriken
 */

# $cameralife and $folder are set by showPage()
# Browses a give path for photos
# Magic vars GET:start, GET/POST:sort are handled in search.class.php

$counts = $folder->getCounts();
$openGraph = $folder->getOpenGraph();
$start = isset($_GET['start']) ? $_GET['start'] : 0;

if (isset($_GET['start']) && is_numeric($_GET['start'])) {
    $folder->setPage($_GET['start'], 25);
} else {
    $folder->setPage(0, 25);
}

if (isset($_GET['page']) && $_GET['page'] == 'folders' || !$counts['photos']) {
    $_GET['page'] = 'folders';
    $results = $folder->getChildren();
} else {
    $_GET['page'] = 'photos';
    $results = $folder->getPhotos();
}

$menu = array();
if ($folder->path()) {
    $menu[] = array(
        'name' => 'Search for ' . $folder->basename(),
        'href' => 'search.php&#63;q=' . $folder->basename(),
        'image' => 'small-search'
    );
}

if ($cameralife->security->Authorize('admin_file')) {
    $menu[] = array(
        'name' => 'Upload photo here',
        'href' => $cameralife->baseURL . '/upload.php&#63;path=' . $folder->path(),
        'image' => 'small-main'
    );
}

$folder_name = $folder->basename()
or $folder_name = '(Top Level)';

$headers = '';
if ($folder->getPrevious()) {
    $headers = "<link rel=\"prev\" href=\"" . $folder->getPrevious() . "\" />\n";
}
if ($folder->getNext()) {
    $headers = "<link rel=\"next\" href=\"" . $folder->getNext() . "\" />\n";
}

$cameralife->theme->header($folder, null, $headers);
?>

<ul class="breadcrumb">
    <?php
    foreach ($folder->getAncestors() as $ancestor) {
        $ancestorOpenGraph = $ancestor->GetOpenGraph();
        echo '  <li><a href="' . htmlspecialchars(
                $ancestorOpenGraph['og:url']
            ) . '"><img alt="folder" src="' . htmlspecialchars(
                $cameralife->iconURL('small-folder')
            ) . '"> ' . htmlentities($ancestorOpenGraph['og:title']) . '</a></li>';
    }
    echo "        <li><img alt=\"folder\" src=\"" . $cameralife->iconURL('small-folder') . "\"> $folder_name</li>\n";
    ?>
</ul>

<?php

if ($counts['photos'] > 0) {
    $sections[] = array('name' => $counts['photos'] . " photos", 'page_name' => 'photos');
}
if ($counts['folders'] > 0) {
    $sections[] = array('name' => $counts['folders'] . " folders", 'page_name' => 'folders');
}

if (count($sections) >= 2) {
    $cameralife->theme->multiSection($sections, $openGraph['og:url']);
} elseif (count($sections == 1)) {
    echo "This folder contains " . strtolower($sections[0]['name']);
} else {
    $cameralife->error('This folder does not exist.');
}

$cameralife->theme->grid($results);

if ($_GET['page'] == "photos") {
    $cameralife->theme->pageSelector($start, $counts['photos'], 25, $openGraph['og:url']);
} else // ($_GET['page'] == "folders")
{
    $cameralife->theme->pageSelector($start, $counts['folders'], 25, $openGraph['og:url']);
}

?>

<form class="form-inline">
    <div class="form-group">
        Sort by
        <select name="sort" class="form-control input-sm" style="width:200px">
            <?php
            $options = $folder->sortOptions();
            foreach ($options as $option) {
                if (isset($option[2])) {
                    echo "    <option " . $option[2] . " value=\"" . $option[0] . "\">" . $option[1] . "</option>\n";
                } else {
                    echo "    <option value=\"" . $option[0] . "\">" . $option[1] . "</option>\n";
                }
            }
            ?>
        </select>
        <input type=submit value="Sort" class="btn btn-default btn-sm">
    </div>
</form>

<?php
$cameralife->theme->footer();
?>
