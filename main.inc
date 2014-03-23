<?php
#if (strpos($_SERVER['REMOTE_ADDR'], '192.168') === false)
#die('site down 4 maintenance');

/**
 * Carries classes CameraLife, CameraLife Module and View
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2013 Will Entriken
 * @access public
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');


// Is this the first run?
if (file_exists(dirname(__FILE__).'/modules/config.inc')) {
  require(dirname(__FILE__).'/modules/config.inc');
} else {
  header('Location: http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']),'/').'/setup/');
  die('Redirecting to installer...');
}

// Is the software newer than the database?
if (!isset($db_schema_version) || $db_schema_version < 4) {
  header('Location: http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']),'/').'/setup/upgrade/upgrade.php');
  die('Redirecting to upgrader...');
}

require dirname(__FILE__).'/modules/module.inc';

class CameraLife extends CameraLifeModule # and View
{
  var $loaded_features;
  var $userpreferences;
  var $version;
  var $base_dir;
  var $base_url; // like http://camera.phor.net or http://phor.net/life/camera
  var $receipt;

  function __construct()
  {
    parent::__construct();  
    if(!ini_get('date.timezone'))
      date_default_timezone_set('GMT');
    ini_set('magic_quotes_runtime', 0);
    ini_set('magic_quotes_sybase', 0);
    $this->base_dir = dirname(__FILE__);
    $this->base_url = 'http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']),'/');
    $this->version = '2.6.7';
    
    $this->preferences[] = array('name'=>'theme', 'type'=>'string', 'default'=>'bootstrap');
    $this->preferences[] = array('name'=>'iconset', 'type'=>'string', 'default'=>'cartoonic');
    $this->preferences[] = array('name'=>'filestore', 'type'=>'string', 'default'=>'local');
    $this->preferences[] = array('name'=>'security', 'type'=>'string', 'default'=>'openid');
    $this->preferences[] = array('name'=>'imageprocessing', 'type'=>'string', 'default'=>'gd');

    $this->preferences[] = array('name'=>'checkpointlogs', 'type'=>'number', 'default'=>'0');
    $this->preferences[] = array('name'=>'checkpointcomments', 'type'=>'number', 'default'=>'0');

    $this->preferences[] = array('name'=>'sitename', 'type'=>'string', 'default'=>'My Photos');
    $this->preferences[] = array('name'=>'siteabbr', 'type'=>'string', 'default'=>'Home');
    $this->preferences[] = array('name'=>'sitedate', 'type'=>'string', 'default'=>date('Y-m-d'));
    $this->preferences[] = array('name'=>'owner_email', 'type'=>'string', 'default'=>'none@none.none');

    $this->preferences[] = array('name'=>'rewrite', 'type'=>'yesno', 'default'=>'no');
    $this->preferences[] = array('name'=>'autorotate', 'type'=>'yesno', 'default'=>'yes');
    $this->preferences[] = array('name'=>'tempdir', 'type'=>'string', 'default'=>'/tmp');
    $this->preferences[] = array('name'=>'thumbsize', 'type'=>'number', 'default'=>'150');
    $this->preferences[] = array('name'=>'scaledsize', 'type'=>'number', 'default'=>'600');
    $this->preferences[] = array('name'=>'optionsizes', 'type'=>'string', 'default'=>'');    
  }
    
  function relativeInclude($file)
  {
    if (file_exists($this->base_dir."/$file"))
      require $this->base_dir."/$file";
    else
      $this->error("File does not exist: ".$this->base_dir."/$file");
  }

  function getModules($feature)
  {
    $modules = glob($this->base_dir."/modules/$feature/*", GLOB_ONLYDIR);
    $modules = array_map('basename', $modules);
    return $modules;
  }

  function getFeature($feature)
  {
    if (!isset($this->loaded_features[$feature]))
      $loaded_features[$feature]='loading';
    elseif ($this->loaded_features[$feature]=='loaded')
        return true;
    elseif ($this->loaded_features[$feature]=='loading')
        $this->error('Circular reference in features :-(');

    $this->relativeInclude("modules/$feature/module.inc");
    $allfeatures = array('core'=>NULL, 'imageprocessing'=>'ImageProcessing', 'security'=>'Security', 'theme'=>'Theme', 'filestore'=>'FileStore');
    $cutename = $allfeatures[$feature];
    
    switch ($feature)
    {
      case 'core': 
        $this->Database = new Database();
        break;
      case 'theme': 
      case 'security': 
      case 'imageprocessing': 
      case 'filestore':
      case 'filestore':
        $implementation = $this->getPref($feature);
        $this->relativeInclude("modules/$feature/$implementation/module.inc");
        $modulename = $implementation.$feature;
        $this->$cutename = new $modulename;
        break;
      default:
        $this->error("Non-existant feature requested: $feature");
    }
    $this->loaded_features[$feature]='loaded';
  }

  function loadPreferences()
  {
    $query = $this->Database->Select('preferences','*');
    while ($result = $query->FetchAssoc())
    {
      $this->userpreferences[$result['prefmodule']][$result['prefkey']] = $result['prefvalue'];
    }
  }

  function savePreferences()
  {
    foreach($this->userpreferences as $module => $moduleprefs)
    {
      if (is_array($moduleprefs))
      {
        foreach($moduleprefs as $key => $value)
        {
          $values = array('prefmodule'=>$module, 'prefkey'=>$key, 'prefvalue'=>$value);
# use cooler SQL here
          $condition = "prefmodule='$module' AND prefkey='$key'";

          $query = $this->Database->Select('preferences', '1', $condition);
          if ($query->FetchAssoc())
            $this->Database->Update('preferences', $values, $condition);
          else
            $this->Database->Insert('preferences', $values);
        }
      }
    }
  }

  public function getOpenGraph()
  {
    global $cameralife;
    $retval = array();
    $retval['og:title'] = $this->getPref('siteabbr');
    $retval['og:type'] = 'website';
    $retval['og:url'] = $this->base_url.'/index.php';
    $retval['og:image'] = $cameralife->iconURL('main');
    $retval['og:image:type'] = 'image/png';
    return $retval;    
  }

  function iconURL($name)
  {
    return $this->base_url."/modules/iconset/".$this->getPref('iconset')."/$name.png";
  }

  function error($message, $file='', $line='')
  {
    global $cameralife;
    echo "<link rel=\"stylesheet\" href=\"//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css\">";
    echo "<div class=\"alert\">\n";
    echo "  <h2>Camera Life has encountered an error!</h2>\n";
    echo "  <blockquote class=\"lead\">".htmlentities($message)."</blockquote>\n";

    if (isset($cameralife->Security) && $cameralife->Security->authorize('admin_customize')) {
      echo "  You can have the backtrace, since you are an admin:\n";
      $calls = debug_backtrace();
      echo "  <dl>\n";
      foreach ($calls as $call) {
        echo "    <dt>{$call['file']}:{$call['line']} ";
        if (isset($call['class'])) {
          $url = "http://camera.phor.net/docs/cameralife/{$call['class']}.html#{$call['function']}";
          echo "<a target=\"_new\" href=\"$url\">".$call['class'].'::'.$call['function']."</a>()</dt>\n";
        } else {
          echo $call['function'];
        }
        echo '    <dd><ul>';
        if (count($call['args']))
          foreach ($call['args'] as $callarg) {
            echo "<li>".print_r($callarg, true)."</li>";
//            var_dump($callarg);
  //          echo "</li>";
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
}

$cameralife = new CameraLife();
$cameralife->getFeature('core');
$cameralife->loadPreferences();

if (isset($GLOBALS['features']) && count($GLOBALS['features']))
foreach($features as $fcn)
  $cameralife->getFeature($fcn);

if (isset($_GET['receipt']) && is_numeric ($_GET['receipt']))
{
  $receipt = new Receipt($_GET['receipt']);
  if ($receipt->isValid())
    $cameralife->receipt = $receipt;
}

####
#
# Convenience functions:
#

/**
 * @param mixed $url an HTML-escaped url 
 * @param mixed $param HTML-escaped param 
 * @param string $value HTML-escaped value
 * @return mixed $url an HTML-escaped url with a get parameter added
 */
function AddParam($url, $param, $value=NULL)
{
  if ((strpos($url, '&#63;') !== FALSE) || (strpos($url, '?') !== FALSE) || (strpos($url, '&amp;') !== FALSE))
    $url .= "&amp;$param";
  else
    $url .= "&#63;$param";

  if ($value !==NULL)
    $url .= "=".rawurlencode($value);

  return $url;
}

function AddParamRawUrlEncode($param, $value=NULL , $url=NULL)
{
  if ($url == NULL)
    $url = $_SERVER['REQUEST_URI'];
  if ((strpos($url, '&#63;') !== FALSE) || (strpos($url, '?') !== FALSE) || (strpos($url, '&amp;') !== FALSE))
    $url .= "&amp;";
  else
    $url .= "&#63;";
  $url .= rawurlencode($param);
  if ($value !==NULL)
    $url .= "=".rawurlencode($value);

  return $url;
}

?>
