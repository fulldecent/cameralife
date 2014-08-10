<?php
namespace CameraLife;

/**
 * Enables search queries
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */

require 'main.inc';
$features = array('theme');
$cameralife = CameraLife::cameraLifeWithFeatures($features);
$search = new Search($_GET['q']);

$numPhotos = $search->getPhotoCount();
$numAlbums = $search->getAlbumCount();
$numFolders = $search->getFolderCount();

## You can search by going to http://camera.phor.net/SEARCHTERM
if (isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] == '404') {
    $webbase = preg_replace('|.*//.*?/|', '', $cameralife->baseURL);
    $query = preg_replace('|.*/|', '', $_SERVER["REQUEST_URI"]);
    header('Location: ' . $cameralife->baseURL . '/search.php?q=' . $query);
    exit(0);
}

## Sometimes we're sure an album page is relevant - redirect there
if (!$numFolders && $numAlbums == 1) {
    $count_term = $cameralife->database->SelectOne('albums', 'COUNT(*)', "term LIKE '" . $_GET['q'] . "'");
    if ($count_term == 1) {
        $albumid = $cameralife->database->SelectOne('albums', 'id', "term LIKE '" . $_GET['q'] . "'");
        header('Location: ' . $cameralife->baseURL . '/album.php?id=' . $albumid);
        echo 'redirecting... ' . $cameralife->baseURL . '/album.php?id=' . $albumid;
        exit(0);
    }
}

## Sometimes we're sure a folder page is relevant - redirect there
if (!$numAlbums && !$numPhotos && $numFolders == 1) {
    list($folder) = $search->getFolders();
    $folderOpenGraph = $folder->GetOpenGraph();
    header('Location: ' . $folderOpenGraph['op:url']);
    exit(0);
}

## If there are no results, set the HTTP status code to 404 
if (!$numAlbums && !$numPhotos && !$numFolders) {
    header("HTTP/1.0 404 Not Found");
}

$search->showPage();
