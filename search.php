<?php
/**Enables search queries
*Optimize the following lines of code accordingly if the query is identical to an existing album
*<code>
*if (!$counts['folders'] && $counts['albums'] == 1)
 * {
 *   $count_term = $cameralife->Database->SelectOne('albums','COUNT(*)',"term LIKE '".$_GET['q']."'");
 *   if ($count_term == 1)
  *  {
  *    $albumid = $cameralife->Database->SelectOne('albums','id',"term LIKE '".$_GET['q']."'");
  *    header('Location: '.$cameralife->base_url.'/album.php?id='.$albumid);
   *   echo 'redirecting...';
  *    exit(0);
   * }
*</code>
*@link http://fdcl.sourceforge.net
 *@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */

/**
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

  #Be intelligent here...
  //"Optimize if this query is identical to an existing album"

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

  $search->ShowPage();
?>


