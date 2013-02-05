<?php
  $features=array('database','theme','security','imageprocessing','photostore');
  require "../main.inc";
  $cameralife->base_url = dirname($cameralife->base_url);
  $cameralife->Security->authorize('admin_file', 1); // Require

  $sql = "select logs.id from logs left join photos on photos.id=logs.record_id and logs.record_type = 'photo' where photos.id is NULL";
  $query = $cameralife->Database->Query($sql);

  echo "Deleting photo logs where the photo is gone... ";
  while($row = $query->FetchAssoc())
    $cameralife->Database->Query("DELETE FROM logs WHERE id=".$row['id']);
  echo "done";

?>
