<?php
/**
 * Carries classes CameraLife, CameraLife Module and View
 * 
 * Also supports running from the command line with arguments
 *  1. dns for database
 *  2. username for database
 *  3. password for database
 * 
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 William Entriken
 * @access public
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('CAMERALIFE_LATEST_SCHEMA_VERSION', '4');

require dirname(__FILE__) . '/modules/module.inc';

class CameraLife extends CameraLifeModule # and View
{
    private $loadedFeatures;
    public $userpreferences;
    public $version;
    public $baseDir;
    public $baseURL; // like http://camera.phor.net or http://phor.net/life/camera
    public $receipt;

    public function __construct()
    {
        parent::__construct();
        if (!ini_get('date.timezone')) {
            date_default_timezone_set('GMT');
        }
        ini_set('magic_quotes_runtime', 0);
        ini_set('magic_quotes_sybase', 0);
        $this->baseDir = dirname(__FILE__);
        if (php_sapi_name() === 'cli') {
            $this->baseURL = 'CLI://';
        } else {
            $this->baseURL = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/');
        }
            
        $this->version = '2.7.0a1';

        $this->preferences[] = array('name' => 'theme', 'type' => 'string', 'default' => 'bootstrap');
        $this->preferences[] = array('name' => 'iconset', 'type' => 'string', 'default' => 'cartoonic');
        $this->preferences[] = array('name' => 'fileStore', 'type' => 'string', 'default' => 'local');
        $this->preferences[] = array('name' => 'security', 'type' => 'string', 'default' => 'openid');
        $this->preferences[] = array('name' => 'imageProcessing', 'type' => 'string', 'default' => 'gd');

        $this->preferences[] = array('name' => 'checkpointlogs', 'type' => 'number', 'default' => '0');
        $this->preferences[] = array('name' => 'checkpointcomments', 'type' => 'number', 'default' => '0');

        $this->preferences[] = array('name' => 'sitename', 'type' => 'string', 'default' => 'My Photos');
        $this->preferences[] = array('name' => 'siteabbr', 'type' => 'string', 'default' => 'Home');
        $this->preferences[] = array('name' => 'sitedate', 'type' => 'string', 'default' => date('Y-m-d'));
        $this->preferences[] = array('name' => 'owner_email', 'type' => 'string', 'default' => 'none@none.none');

        $this->preferences[] = array('name' => 'rewrite', 'type' => 'yesno', 'default' => 'no');
        $this->preferences[] = array('name' => 'autorotate', 'type' => 'yesno', 'default' => 'yes');
        $this->preferences[] = array('name' => 'tempdir', 'type' => 'string', 'default' => '/tmp');
        $this->preferences[] = array('name' => 'thumbsize', 'type' => 'number', 'default' => '150');
        $this->preferences[] = array('name' => 'scaledsize', 'type' => 'number', 'default' => '600');
        $this->preferences[] = array('name' => 'optionsizes', 'type' => 'string', 'default' => '');
    }

    public function relativeInclude($file)
    {
        if (file_exists($this->baseDir . "/$file")) {
            require $this->baseDir . "/$file";
        } else {
            $this->error("File does not exist: " . $this->baseDir . "/$file");
        }
    }

    public function getModules($feature)
    {
        $modules = glob($this->baseDir . "/modules/$feature/*", GLOB_ONLYDIR);
        $modules = array_map('basename', $modules);
        return $modules;
    }

    public function getFeature($feature)
    {
        if (!isset($this->loadedFeatures[$feature])) {
            $loadedFeatures[$feature] = 'loading';
        } elseif ($this->loadedFeatures[$feature] == 'loaded') {
            return;
        } elseif ($this->loadedFeatures[$feature] == 'loading') {
            $this->error('Circular reference in features :-(');
        }

        $allfeatures = array('core', 'imageProcessing', 'security', 'theme', 'fileStore');
        if (!in_array($feature, $allfeatures)) {
            $this->error("Non-existant feature requested: $feature");
        }
        $this->relativeInclude('modules/' . strtolower($feature) . '/module.inc');
        if ($feature != 'core') { // Need to load implementation
//TODO should core even be a "feature?"
            $implementation = $this->getPref($feature);
            $this->relativeInclude('modules/' . strtolower($feature) . "/$implementation/module.inc");
            $modulename = $implementation . $feature;
            $this->$feature = new $modulename($this);
        }
        $this->loadedFeatures[$feature] = 'loaded';
    }

    public function loadPreferences()
    {
        $query = $this->database->Select('preferences', '*');
        while ($result = $query->fetchAssoc()) {
            $this->userpreferences[$result['prefmodule']][$result['prefkey']] = $result['prefvalue'];
        }
    }

    public function savePreferences()
    {
        foreach ($this->userpreferences as $module => $moduleprefs) {
            if (is_array($moduleprefs)) {
                foreach ($moduleprefs as $key => $value) {
                    $values = array('prefmodule' => $module, 'prefkey' => $key, 'prefvalue' => $value);
# use cooler SQL here
                    $condition = "prefmodule='$module' AND prefkey='$key'";

                    $query = $this->database->Select('preferences', '1', $condition);
                    if ($query->fetchAssoc()) {
                        $this->database->Update('preferences', $values, $condition);
                    } else {
                        $this->database->Insert('preferences', $values);
                    }
                }
            }
        }
    }

    public function getOpenGraph()
    {
        $retval = array();
        $retval['og:title'] = $this->getPref('siteabbr');
        $retval['og:type'] = 'website';
        $retval['og:url'] = $this->baseURL . '/index.php';
        $retval['og:image'] = $this->iconURL('main');
        $retval['og:image:type'] = 'image/png';
        return $retval;
    }

    public function iconURL($name)
    {
        return $this->baseURL . "/modules/iconset/" . $this->getPref('iconset') . "/$name.png";
    }

    public function error($message)
    {
//        global $cameralife;
        echo "<link rel=\"stylesheet\" href=\"//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css\">";
        echo "<div class=\"alert\">\n";
        echo "  <h2>Camera Life has encountered an error!</h2>\n";
        echo "  <blockquote class=\"lead\">" . htmlentities($message) . "</blockquote>\n";

        if (isset($this->security) && $this->security->authorize('admin_customize')) {
            echo "  <p class=\"alert alert-info\">You can have the backtrace, since you are an admin:</p><hr>\n";
            $calls = debug_backtrace();
            echo "  <dl>\n";
            foreach ($calls as $call) {
                $callText = $call['function'];
                if (isset($call['class'])) {
                    $url = "http://camera.phor.net/docs/cameralife/{$call['class']}.html#{$call['function']}";
                    $callText = "<a target=\"_new\" href=\"$url\">" . $call['class'] . '::' . $call['function'] . "</a>()";
                }
                echo "    <dt>{$call['file']}:{$call['line']} $callText</dt>";
                echo '    <dd><ul>';
                if (count($call['args'])) {
                    foreach ($call['args'] as $callarg) {
                        echo "<li>" . print_r($callarg, true) . "</li>";
                    }
                }
                echo "</ul></dd>\n";
#       echo '<dd><p>For object details view source...</p><!--<pre>';
#       var_dump($call['object']);
#       echo '--></pre></dd>';
            }
            echo "  </dl>\n";
        }
        echo "<p><a href=\"https://github.com/fulldecent/cameralife/issues/new\" class=\"btn\">Report to Camera Life Project</a></p>";
        echo "</div>\n";
        exit(1);
    }
    
    public static function cameraLifeWithFeatures($features = array())
    {
        global $_GET, $db_dsn, $db_pass, $db_name, $db_prefix;
        
        if (php_sapi_name() === 'cli' && is_numeric($_SERVER['argc']) && $_SERVER['argc'] >= 2)
        {
            // Configure database using argv
            $db_dsn = $_SERVER['argv'][1];
            $db_pass = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : '';
            $db_name = isset($_SERVER['argv'][3]) ? $_SERVER['argv'][3] : '';
            $db_prefix = isset($_SERVER['argv'][4]) ? $_SERVER['argv'][4] : '';
            $db_schema_version = CAMERALIFE_LATEST_SCHEMA_VERSION;
        }
    
        // Bootstrap /////////////////////
        // Is this the first run?
        if (file_exists(dirname(__FILE__) . '/modules/config.inc')) {
            require(dirname(__FILE__) . '/modules/config.inc');
        } elseif (!isset($db_dsn)) {
            $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $url = ($secure?'https://':'http://').$_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/') . '/setup/';
            header("Location: $url");
            die('Redirecting to installer...');
        }
        
        // Is the software newer than the database?
        if (!isset($db_schema_version) || $db_schema_version < CAMERALIFE_LATEST_SCHEMA_VERSION) {
            header(
                'Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(
                    dirname($_SERVER['PHP_SELF']),
                    '/'
                ) . '/setup/upgrade/upgrade.php'
            );
            die('Redirecting to upgrader...');
        }
        
        // Factory /////////////////////
        $cameralife = new CameraLife();
        $cameralife->getFeature('core');
        $cameralife->database = new Database($cameralife);
        $cameralife->loadPreferences();
        if (isset($features) && count($features)) {
            foreach ($features as $feature) {
                $cameralife->getFeature($feature);
            }
        }
        
        if (isset($_GET['receipt']) && is_numeric($_GET['receipt'])) {
            $receipt = new Receipt($_GET['receipt']);
            if ($receipt->isValid()) {
                $cameralife->receipt = $receipt;
            }
        }
        return $cameralife;
    }
}
