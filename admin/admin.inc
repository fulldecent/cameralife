<?php
/**
 * Common features in the admin/ directory
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 William Entriken
 * @access public
 */

/**
 * Reads module preferences (format defined in
 * CameraLifeModule::preferences) and renders
 * HTML for the user to set these prefs
 */
function renderPrefsAsHTML($module)
{
    global $cameralife;

    echo "<h2>Settings for " . $module->name . "</h2>\n";
    echo "<form class=\"form-horizontal\" method=\"post\" action=\"controller_prefs.php\">\n";
    echo "<input type=\"hidden\" name=\"target\" value=\"" . $_SERVER['PHP_SELF'] . "\" />\n";
    if (isset($module->about)) {
        echo "<p>" . $module->about . "</p>\n";
    }
    echo "<table>\n";

    $prefnum = 0;
    if (!count($module->preferences)) {
        echo "<p>(no settings for this module)</p>\n";
    } else {
        foreach ($module->preferences as $pref) {
            $prefnum++;
            echo '<div class="form-group">';
            echo '  <label class="col-lg-2 control-label" for="' . $pref['name'] . '">' . $pref['name'] . '</label>';
            echo '  <div class="col-lg-10">';
            echo "    <input type=\"hidden\" name=\"module$prefnum\" value=\"" . get_class($module) . "\" />\n";
            echo "    <input type=\"hidden\" name=\"param$prefnum\" value=\"" . $pref['name'] . "\" />\n";

            $value = $module->getPref($pref['name']);

            if ($pref['type'] == 'number') {
                echo "      <input class=\"form-control\" type=\"number\" name=\"value$prefnum\" value=\"$value\" />\n";
            } elseif ($pref['type'] == 'string') {
                echo "      <input class=\"form-control\" type=\"text\" name=\"value$prefnum\" value=\"$value\" />\n";
            }
            if ($pref['type'] == 'directory' || $pref['type'] == 'directoryrw') {
                echo "      <input class=\"form-control\" type=\"text\" name=\"value$prefnum\" value=\"$value\" />\n";
                if (!is_dir($value) && !is_dir($cameralife->baseDir . "/$value")) {
                    echo '<p class="text-error">This is not a directory</p>';
                } elseif ($pref['type'] == 'directoryrw' && !is_writable($value) && !is_writable(
                        $cameralife->baseDir . "/$value"
                    )
                ) {
                    echo '<p class="text-error">This directory is not writable</p>';
                }
            } elseif (is_array($pref['type'])) { // enumeration
                echo "      <select class=\"form-control\" name=\"value$prefnum\">\n";
                foreach ($pref['type'] as $index => $desc) {
                    if ($index == $value) {
                        echo "        <option selected value=\"$index\">$desc</option>\n";
                    } else {
                        echo "        <option value=\"$index\">$desc</option>\n";
                    }
                }
                echo "      </select />\n";
            } elseif ($pref['type'] == 'yesno') {
                echo "      <select name=\"value$prefnum\">\n";
                foreach (array('1' => 'Yes', '0' => 'No') as $index => $desc) {
                    if ($index == $value) {
                        echo "        <option class=\"form-control\" selected value=\"$index\">$desc</option>\n";
                    } else {
                        echo "        <option value=\"$index\">$desc</option>\n";
                    }
                }
                echo "      </select />\n";
            }
            echo '    <span class="help-inline">' . $pref['desc'] . '</span>';
            echo '  </div>';
            echo '</div>';
        }
    }

    if (count($module->preferences)) {
        echo '<div class="control-group"><div class="controls"><input type="submit" value="Save changes" class="btn btn-primary"/></div></div>';
    }
    echo "</form>\n";
}

