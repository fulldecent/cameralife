<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;

/**
 * Simple view for rendering a list of configurable preferences
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class AdminPreferencesView extends View
{
    public $moduleName;

    /**
     * preferences
     *
     * @var array with entries like:
     *   'module' => 'CameraLife'
     *   'name' => 'theme'
     *   'type' => 'string'
     *   'default' => 'bootstrap'
     *   'description' => 'Choose a pluggable theme'
     *
     * @access public
     */
    public $preferences;

    public function render()
    {

        echo "<h2>Settings for " . $this->moduleName . "</h2>\n";
        if (isset($module->about)) {
            echo "<p class=\"lead\">" . $module->about . "</p>\n";
        }
        if (!count($this->preferences)) {
            echo "<p>(no settings for this module)</p>\n";
            return;
        }

        ///todo set url
        echo "<form class=\"form-horizontal\" method=\"post\" action=\"controller_prefs.php\">\n";
        echo "<input type=\"hidden\" name=\"target\" value=\"" . $_SERVER['PHP_SELF'] . "\" />\n";

        foreach ($this->preferences as $pref) {
            $tag = $pref['module'] . '|' . $pref['key'];

            echo '<div class="form-group">';
            echo '  <label class="col-md-2 control-label" for="' . $tag . '">' . $pref['name'] . '</label>';
            echo '  <div class="col-md-10 form-inline">' . PHP_EOL;
            $value = Models\Preferences::valueForModuleWithKey($pref['module'], $pref['key']);

            if ($pref['type'] == 'number') {
                echo "      <input class=\"form-control\" type=\"number\" name=\"$tag\" value=\"$value\" />\n";
            } elseif ($pref['type'] == 'string') {
                echo "      <input class=\"form-control\" type=\"text\" name=\"$tag\" value=\"" . htmlspecialchars(
                    $value
                ) . "\" />\n";
            }
            if ($pref['type'] == 'directory' || $pref['type'] == 'directoryrw') {
                echo "      <input class=\"form-control\" type=\"text\" name=\"$tag\" value=\"$value\" />\n";
                if (!is_dir($value) && !is_dir(constant('BASE_DIR') . "/$value")) {
                    echo '<p class="text-error">This is not a directory</p>';
                } elseif ($pref['type'] == 'directoryrw' && !is_writable($value) && !is_writable(
                    constant('BASE_DIR') . "/$value"
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
            if (isset($pref['desc'])) {
                echo '    <span class="text-muted">' . $pref['desc'] . '</span>';
            }
            echo '  </div>';
            echo '</div>' . PHP_EOL;
        }

        echo '<div class="control-group"><div class="controls"><input type="submit" value="Save changes" class="btn btn-primary"/></div></div>';
        echo "</form>\n";
    }
}
