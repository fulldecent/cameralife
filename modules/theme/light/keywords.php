<?php
  # Output a javascript file to use in conjunction with
  # the keyword tagging system
  /**Output a javascript file to use in conjunction with the keyword tagging system
  *@link http://fdcl.sourceforge.net
  *@version 2.6.3b6
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */

  /**
  */
  $features=array('database', 'security');
  require "../../../main.inc";

  if (!$cameralife->Security->authorize('admin_file'))
    $condition = 'AND status=0';
#  $selection = "LOWER(CONCAT(keywords, ' ', description)) as keywords";
#  $condition = "keywords != '' OR description != '' $condition";
  $selection = "keywords";
  $condition = "keywords != '' $condition";
  $keyquery = $cameralife->Database->Select('photos', $selection, $condition);

  $keys = array();
  while ($row = $keyquery->FetchAssoc())
  {
    foreach (preg_split('|[^a-z0-9]+|i', $row['keywords']) as $keyword)
    $keys[$keyword]++;
# Check for unique valules, even if different case
  }

  header("Content-type: text/javascript");
  header("Date: ".gmdate("D, d M Y H:i:s", time())." GMT");
  header("Expires: ".gmdate("D, d M Y H:i:s", time() + 3600)." GMT"); // One hour

  echo "var tags=({";
  /**Need description for function balls
  */
  function balls($key, $count) { return "\"$key\":$count"; }
  echo join(array_map('balls', array_keys($keys),array_values($keys)),',');
  echo "})";
?>
