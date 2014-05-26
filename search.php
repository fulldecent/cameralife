<?php
namespace CameraLife;

/**
 * Enables search queries
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */

require 'main.inc';
$features = array('theme');
$cameralife = CameraLife::cameraLifeWithFeatures($features);
$search = new Search($_GET['q']);

/* Bonus code to log searches
/$log_handle = fopen ("search.log", "a");
fwrite($log_handle, $_GET["q"]."\n");
fclose ($log_handle);
*/

$counts = $search->getCounts();

## You can search by going to http://camera.phor.net/SEARCHTERM
if (isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] == '404') {
    $webbase = preg_replace('|.*//.*?/|', '', $cameralife->baseURL);
    $query = preg_replace('|.*/|', '', $_SERVER["REQUEST_URI"]);
    header('Location: ' . $cameralife->baseURL . '/search.php?q=' . $query);
    exit(0);
}

## Sometimes we're sure an album page is relevant - redirect there
if (!$counts['folders'] && $counts['albums'] == 1) {
    $count_term = $cameralife->database->SelectOne('albums', 'COUNT(*)', "term LIKE '" . $_GET['q'] . "'");
    if ($count_term == 1) {
        $albumid = $cameralife->database->SelectOne('albums', 'id', "term LIKE '" . $_GET['q'] . "'");
        header('Location: ' . $cameralife->baseURL . '/album.php?id=' . $albumid);
        echo 'redirecting... ' . $cameralife->baseURL . '/album.php?id=' . $albumid;
        exit(0);
    }
}

## Sometimes we're sure a folder page is relevant - redirect there
if (!$counts['albums'] && !$counts['photos'] && $counts['folders'] == 1) {
    list($folder) = $search->getFolders();
    $folderOpenGraph = $folder->GetOpenGraph();
    header('Location: ' . $folderOpenGraph['op:url']);
    exit(0);
}

$search->showPage();
