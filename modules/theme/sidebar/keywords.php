<?php
/**
*Uses a keyword tagging system
*<ul>
*<lI>Outputs a javascript file</li>
*<li>The javascript is used in conjunction with the keyword tagging system</li>
*<li>Checks for unique values even if the cases are different</li>
*</ul>
* @link http://fdcl.sourceforge.net
*@version 2.6.2
*@author Will Entriken<cameralife@phor.net>
*@access public
*@copyright Copyright (c) 2001-2009 Will Entriken
*/
/**
*/
/**
*/
# Output a javascript file to use in conjunction with
  # the keyword tagging system
  $features=array('database', 'security');
  require "../../../main.inc";

  if (!$cameralife->Security->authorize('admin_file'))
    $condition = 'AND status=0';
#  $selection = "LOWER(CONCAT(keywords, ' ', description)) as keywords";
#  $condition = "keywords != '' OR description != '' $condition";
  $selection = "keywords";s
  $condition = "keywords != '' $condition";
  $keyquery = $cameralife->Database->Select('photos', $selection, $condition);

  $keys = array();
  while ($row = $keyquery->FetchAssoc())
  {
    foreach (preg_split('|[^a-z0-9]+|i', $row['keywords']) as $keyword)
    $keys[$keyword]++;
# Check for unique valules, even if different case

  }

  header("Date: ".gmdate("D, d M Y H:i:s", time())." GMT");
  header("Expires: ".gmdate("D, d M Y H:i:s", time() + 3600)." GMT"); // One hour

  echo "var tags=({";
  function balls($key, $count) { return "\"$key\":$count"; }
  echo join(array_map('balls', array_keys($keys),array_values($keys)),',');
  echo "})";
?>
