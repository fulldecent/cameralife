<?php
/**
 * Enables search queries
 * @link http://fdcl.sourceforge.net
 * @version 
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

$features=array('database','theme');
require "main.inc";

$search = new Search($_GET['q']);

/* Bonus code to log searches
/$log_handle = fopen ("search.log", "a");
fwrite($log_handle, $_GET["q"]."\n");
fclose ($log_handle);
*/

$counts = $search->GetCounts();

## You can search by going to http://camera.phor.net/SEARCHTERM
if ($_SERVER['REDIRECT_STATUS'] == '404')
{
  $webbase = preg_replace('|.*//.*?/|', '', $cameralife->base_url);
  $query = preg_replace('|.*/|','',$_SERVER["REQUEST_URI"]);
  header('Location: '.$cameralife->base_url.'/search.php?q='.$query);
  exit(0);
}

## Sometimes we're sure an album page is relevant - redirect there
if (!$counts['folders'] && $counts['albums'] == 1)
{
  $count_term = $cameralife->Database->SelectOne('albums','COUNT(*)',"term LIKE '".$_GET['q']."'");
  if ($count_term == 1)
  {
    $albumid = $cameralife->Database->SelectOne('albums','id',"term LIKE '".$_GET['q']."'");
    header('Location: '.$cameralife->base_url.'/album.php?id='.$albumid);
    echo 'redirecting... '.$cameralife->base_url.'/album.php?id='.$albumid;
    exit(0);
  }
}

## Sometimes we're sure a folder page is relevant - redirect there
if (!$counts['albums'] && !$counts['photos'] && $counts['folders'] == 1)
{
  list($folder) = $search->GetFolders();
  $icon = $folder->GetIcon();
  header('Location: '.$icon['href']);
  exit(0);
}

$search->ShowPage();
?>


