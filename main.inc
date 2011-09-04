<?php
#if (strpos($_SERVER['REMOTE_ADDR'], '192.168') === false)
#die('site down 4 maintenance');

/**
 * Carries classes CameraLife, CameraLife Module and View
 * @link http://fdcl.sourceforge.net
 * @version 
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */
#error_reporting(E_ALL);
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');


// Is this the first run?
if (file_exists(dirname(__FILE__).'/modules/config.inc'))
{
  require(dirname(__FILE__).'/modules/config.inc');
} else {
  header('Location: http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']),'/').'/setup/');
  die('Redirecting to installer...');
}

// Is the software newer than the database?
if (!isset($db_schema_version) || $db_schema_version < 2)
{
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

  function CameraLife()
  {
    ini_set('magic_quotes_runtime', 0);
    ini_set('magic_quotes_sybase', 0);
    $this->base_dir = dirname(__FILE__);
    $this->base_url = 'http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']),'/');

    $this->version = '2.6.3b3';

    // Load preferences
    $this->GetFeature('core');
    $this->GetFeature('database');
    $this->LoadPreferences();

    $this->preferences[] = array('name'=>'theme', 'type'=>'string', 'default'=>'sidebar');
    $this->preferences[] = array('name'=>'iconset', 'type'=>'string', 'default'=>'cartoonic');
    $this->preferences[] = array('name'=>'photostore', 'type'=>'string', 'default'=>'local');
    $this->preferences[] = array('name'=>'security', 'type'=>'string', 'default'=>'default');
    $this->preferences[] = array('name'=>'imageprocessing', 'type'=>'string', 'default'=>'gd');

    $this->preferences[] = array('name'=>'checkpointlogs', 'type'=>'number', 'default'=>'0');
    $this->preferences[] = array('name'=>'checkpointcomments', 'type'=>'number', 'default'=>'0');

    $this->preferences[] = array('name'=>'sitename', 'type'=>'string', 'default'=>'My Photos');
    $this->preferences[] = array('name'=>'siteabbr', 'type'=>'string', 'default'=>'Home');
    $this->preferences[] = array('name'=>'sitedate', 'type'=>'string', 'default'=>date('Y-m-d'));
    $this->preferences[] = array('name'=>'owner_email', 'type'=>'string', 'default'=>'none@none.none');

    $this->preferences[] = array('name'=>'rewrite', 'type'=>'yesno', 'default'=>'no');
    $this->preferences[] = array('name'=>'iphone', 'type'=>'yesno', 'default'=>'yes');
    $this->preferences[] = array('name'=>'autorotate', 'type'=>'yesno', 'default'=>'yes');
    $this->preferences[] = array('name'=>'tempdir', 'type'=>'string', 'default'=>'/tmp');
    $this->preferences[] = array('name'=>'thumbsize', 'type'=>'number', 'default'=>'150');
    $this->preferences[] = array('name'=>'scaledsize', 'type'=>'number', 'default'=>'600');
    $this->preferences[] = array('name'=>'optionsizes', 'type'=>'string', 'default'=>'');
  }  
    
  function RelativeInclude($file)
  {
    require $this->base_dir."/$file";
  }

  function GetModules($feature)
  {
    $modules = glob($this->base_dir."/modules/$feature/*", GLOB_ONLYDIR);
    $modules = array_map('basename', $modules);
    return $modules;
  }

  function GetFeature($feature)
  {
    if (!isset($this->loaded_features[$feature]))
      $loaded_features[$feature]='loading';
    elseif ($this->loaded_features[$feature]=='loaded')
        return true;
    elseif ($this->loaded_features[$feature]=='loading')
        $this->Error('Circular reference in features :-(');

    $this->RelativeInclude("modules/$feature/module.inc");
    $allfeatures = array('core'=>NULL, 'database'=>'Database', 'imageprocessing'=>'ImageProcessing', 'security'=>'Security', 'theme'=>'Theme', 'photostore'=>'PhotoStore');
    $cutename = $allfeatures[$feature];
    
    switch ($feature)
    {
      case 'core': 
        break;
      case 'database': 
        $this->RelativeInclude('modules/database/mysql/module.inc');
        $this->Database = new MysqlDatabase() ;
          break;
      case 'theme': 
        if ($this->GetPref('iphone') && (stristr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || stristr($_SERVER['HTTP_USER_AGENT'], 'iPod')) )
          $implementation = 'iphone';
        else 
          $implementation = $this->GetPref('theme');
        $this->RelativeInclude("modules/$feature/$implementation/module.inc");
        $modulename = $implementation.$feature;
        $this->$cutename = new $modulename;
        break;
      case 'security': 
      case 'imageprocessing': 
      case 'photostore':
        $implementation = $this->GetPref($feature);
        $this->RelativeInclude("modules/$feature/$implementation/module.inc");
        $modulename = $implementation.$feature;
        $this->$cutename = new $modulename;
        break;
      default:
        $this->Error("Non-existant feature requested: $feature");
    }
    $this->loaded_features[$feature]='loaded';
  }

  function LoadPreferences()
  {
    $query = $this->Database->Select('preferences','*');
    while ($result = $query->FetchAssoc())
    {
      $this->userpreferences[$result['prefmodule']][$result['prefkey']] = $result['prefvalue'];
    }
  }

  function SavePreferences()
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

  function GetIcon($size='large')
  {
    return array("name"=>$this->GetPref('siteabbr'),
                 "href"=>$this->base_url.'/index.php',
                 'image'=>($size=='large')?'main':'small-main',
                 'rel'=>'home');
  }

  function IconURL($name)
  {
    return $this->base_url."/modules/iconset/".$this->GetPref('iconset')."/$name";
  }

  function Error($message, $file='', $line='')
  {
    global $cameralife;

    echo '<div style="margin: 3em; background: gray; border: 2px solid gray; padding:0.5em">';
    echo '  <div style="text-align:center; color: white; font-size:large">';
    echo '    Camera Life has encountered an error!</div>';
    echo "  <div style=\"background: #eee; padding: 1em;\">$message</div>\n";
    if ($file)
      echo "  <div style=\"background: #eee; padding: 1em;\">Error at: <i>$file</i></div>\n";
    if ($line)
      echo "  <div style=\"background: #eee; padding: 1em;\">Line: <i>$line</i></div>\n";
    echo "</div></div>\n";

    if ($cameralife->Security && $cameralife->Security->authorize('admin_customize'))
    {
      echo "You can have the details, since you are an admin:<pre>\n";
      echo "</pre>\n";
      $calls = debug_backtrace();
      echo "<dl>\n";
      foreach ($calls as $call)
      {
        echo "<dt>{$call['file']}:{$call['line']}<br />\n";
        $url = "http://fdcl.sourceforge.net/docs/cameralife/{$call['class']}.html#{$call['function']}";
        echo "<a target=\"_new\" href=\"$url\">".$call['class'].'::'.$call['function']."</a>()</dt>";
        echo '<dd><ul>';
        if(count($call['args']))
          foreach($call['args'] as $callarg) 
          {
            echo "<li><pre>";
            var_dump($callarg);
            echo "</pre></li>";
          }
        echo '</ul></dd>';
#       echo '<dd><p>For object details view source...</p><!--<pre>';
#       var_dump($call['object']);
#       echo '--></pre></dd>';
      }
      echo "</dl>\n";

    }
    exit(1);
  }
}

$cameralife = new CameraLife();

if (count($GLOBALS['features']))
foreach($features as $fcn)
  $cameralife->GetFeature($fcn);

if (isset($_GET['receipt']) && is_numeric ($_GET['receipt']))
{
  $receipt = new Receipt($_GET['receipt']);
  if ($receipt->IsValid())
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
  if ((strpos($url, '&#63;') !== FALSE) || (strpos($url, '?') !== FALSE))
    $url .= "&amp;$param";
  else
    $url .= "&#63;$param";

  if ($value !==NULL)
    $url .= "=$value";

  return $url;
}


?>
