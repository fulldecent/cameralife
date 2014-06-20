<?php
namespace CameraLife;
/**
 * Common features in the admin/ directory
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
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
    if (isset($module->about)) {
        echo "<p class=\"lead\">" . $module->about . "</p>\n";
    }
    if (!count($module->preferences)) {
        echo "<p>(no settings for this module)</p>\n";
        return;
    }

    echo "<form class=\"form-horizontal\" method=\"post\" action=\"controller_prefs.php\">\n";
    echo "<input type=\"hidden\" name=\"target\" value=\"" . $_SERVER['PHP_SELF'] . "\" />\n";
    echo "<table>\n";

    foreach ($module->preferences as $pref) {
        $tag = get_class($module) . '|' . $pref['name'];
        echo '<div class="form-group">';
        echo '  <label class="col-md-2 control-label" for="' . $tag . '">' . $pref['name'] . '</label>';
        echo '  <div class="col-md-10 form-inline">';
        $value = $module->getPref($pref['name']);

        if ($pref['type'] == 'number') {
            echo "      <input class=\"form-control\" type=\"number\" name=\"$tag\" value=\"$value\" />\n";
        } elseif ($pref['type'] == 'string') {
            echo "      <input class=\"form-control\" type=\"text\" name=\"$tag\" value=\"" . htmlspecialchars(
                $value
            ) . "\" />\n";
        }
        if ($pref['type'] == 'directory' || $pref['type'] == 'directoryrw') {
            echo "      <input class=\"form-control\" type=\"text\" name=\"$tag\" value=\"$value\" />\n";
            if (!is_dir($value) && !is_dir($cameralife->baseDir . "/$value")) {
                echo '<p class="text-error">This is not a directory</p>';
            } elseif ($pref['type'] == 'directoryrw' && !is_writable($value) && !is_writable(
                $cameralife->baseDir . "/$value"
            )
            ) {
                echo '<p class="text-error">This directory is not writable</p>';
            }
        } elseif (is_array($pref['type'])) {
// enumeration
            echo "      <select class=\"form-control\" name=\"$tag\">\n";
            foreach ($pref['type'] as $index => $desc) {
                $extra = $index == $value ? 'selected' : '';
                echo "        <option $extra value=\"$index\">$desc</option>\n";
            }
            echo "      </select />\n";
        } elseif ($pref['type'] == 'yesno') {
            echo "      <select name=\"$tag\">\n";
            foreach (array('1' => 'Yes', '0' => 'No') as $index => $desc) {
                if ($index == $value) {
                    echo "        <option class=\"form-control\" selected value=\"$index\">$desc</option>\n";
                } else {
                    echo "        <option value=\"$index\">$desc</option>\n";
                }
            }
            echo "      </select />\n";
        }
        echo '    <span class="text-muted">' . $pref['desc'] . '</span>';
        echo '  </div>';
        echo '</div>';
    }

    echo '<div class="control-group"><div class="controls"><input type="submit" value="Save changes" class="btn btn-primary"/></div></div>';
    echo "</form>\n";
}
