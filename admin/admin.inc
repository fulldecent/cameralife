<?php
/** Common features in the admin/ directory
 *@link http://fdcl.sourceforge.net
 *@version 2.6.3
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 */

/**
 * Reads module preferences (format defined in 
 * CameraLifeModule::preferences) and renders
 * HTML for the user to set these prefs
 */
function renderPrefsAsHTML($module)
{
  global $cameralife;

  echo "<form method=\"post\" action=\"controller_prefs.php\">\n";
  echo "<input type=\"hidden\" name=\"target\" value=\"".$_SERVER['PHP_SELF']."\" />\n";
  echo "<h2>Settings for ".$module->name."</h2>\n";
  echo "<p>".$module->about."</p>\n";
  echo "<table>\n";

  $prefnum=0;
  if (!count($module->preferences))
    echo "<tr><td>(no settings for this module)</td></tr>\n";
  else
  foreach ($module->preferences as $pref)
  {
    $prefnum++;
    echo "  <tr><td>".$pref['desc']."\n";
    echo "    <td>\n";
    echo "      <input type=\"hidden\" name=\"module$prefnum\" value=\"".get_class($module)."\" />\n";
    echo "      <input type=\"hidden\" name=\"param$prefnum\" value=\"".$pref['name']."\" />\n";

    $value = $module->GetPref($pref['name']);

    if ($pref['type'] == 'number' || $pref['type'] == 'string' || $pref['type'] == 'directory' || $pref['type'] == 'directoryrw')
    {
      echo "      <input type=\"text\" name=\"value$prefnum\" value=\"$value\" />\n";
      if (($pref['type'] == 'directory' || $pref['type'] == 'directoryrw' ) && !is_dir($value) && !is_dir($cameralife->base_dir."/$value"))
        echo "<br /><span class=\"alert\">This is not a directory</span>\n";
      elseif ($pref['type'] == 'directoryrw'  && !is_writable($value) && !is_writable($cameralife->base_dir."/$value"))
        echo "<br /><span class=\"alert\">This directory is not writable</span>\n";
    }
    elseif (is_array($pref['type'])) // enumeration
    {
      echo "      <select name=\"value$prefnum\" />\n";
      foreach($pref['type'] as $index=>$desc)
      {
        if ($index == $value)
          echo "        <option selected value=\"$index\">$desc</option>\n";
        else
          echo "        <option value=\"$index\">$desc</option>\n";
      }
      echo "      </select />\n";
    }
    elseif ($pref['type'] == 'yesno')
    {
      echo "      <select name=\"value$prefnum\" />\n";
      foreach(array('1'=>'Yes', '0'=>'No') as $index=>$desc)
      {
        if ($index == $value)
          echo "        <option selected value=\"$index\">$desc</option>\n";
        else
          echo "        <option value=\"$index\">$desc</option>\n";
      }
      echo "      </select />\n";
    }
  }

  if (count($module->preferences))
    echo "<tr><td><td><input type=\"submit\" value=\"Save changes\" />\n";
  echo "</table>\n";
  echo "</form>\n";
}

