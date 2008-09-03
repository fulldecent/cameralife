<?php
#if (strpos($_SERVER['REMOTE_ADDR'], '192.168') === false)
#die('site down 4 maintenance');

  // Is this the first run?
  if (file_exists(dirname(__FILE__).'/modules/config.inc'))
  {
    require(dirname(__FILE__).'/modules/config.inc');
  } else {
    header('Location: http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']),'/').'/setup/');
    die('Redirecting to installer...');
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

      $this->version = '2.6.2b3';

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
    }  
      
    function RelativeInclude($file)
    {
      require $this->base_dir."/$file";
    }

    function GetFeature($feature)
    {
      if ($this->loaded_features[$feature]=='loaded')
        return true;
      if ($this->loaded_features[$feature]=='loading')
        $this->Error('Circular reference in features :-(');
      $loaded_features[$feature]='loading';

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
// use cooler SQL here
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
#        var_dump(debug_backtrace());
        echo "</pre>\n";
        $calls = debug_backtrace();
        echo "<dl>\n";
        foreach ($calls as $call)
        {
          echo "<dt>{$call['file']}:{$call['line']}<br />\n";
          if ($call['class'])
            echo $call['class'].'::';
          echo $call['function']."</dt>";
          echo '<dd><pre>';
          var_dump($call['args']);
          echo '</pre></dd>';
          echo '<dd><p>For object details view source...</p><!--<pre>';
          var_dump($call['object']);
          echo '--></pre></dd>';
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

  if (is_numeric ($_GET['receipt']))
  {
    $receipt = new Receipt($_GET['receipt']);
    if ($receipt->IsValid())
      $cameralife->receipt = $receipt;
  }
?>
