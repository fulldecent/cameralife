<?php
  $features=array('database','theme');
  require "main.inc";

  $search = new Search($_GET['q']);

  /* Bonus code to log searches
  $log_handle = fopen ("search.log", "a");
  fwrite($log_handle, $_GET["q"]."\n");
  fclose ($log_handle);
  */

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

  $search->ShowPage();
?>
