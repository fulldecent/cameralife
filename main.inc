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

  class CameraLife extends CameraLifeModule
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

      $this->version = '2.6.1';

      // Load preferences
      $this->GetFeature('core');
      $this->GetFeature('database');
      $this->LoadPreferences();

      $this->preferences[] = array('name'=>'theme', 'type'=>'string', 'default'=>'sidebar');
      $this->preferences[] = array('name'=>'iconset', 'type'=>'string', 'default'=>'cartoonic');
      $this->preferences[] = array('name'=>'photostore', 'type'=>'string', 'default'=>'local');
      $this->preferences[] = array('name'=>'security', 'type'=>'string', 'default'=>'defaultsecurity');

      $this->preferences[] = array('name'=>'checkpointlogs', 'type'=>'number', 'default'=>'0');
      $this->preferences[] = array('name'=>'checkpointcomments', 'type'=>'number', 'default'=>'0');

      $this->preferences[] = array('name'=>'sitename', 'type'=>'string', 'default'=>'My Photos');
      $this->preferences[] = array('name'=>'siteabbr', 'type'=>'string', 'default'=>'Home');
      $this->preferences[] = array('name'=>'sitedate', 'type'=>'string', 'default'=>'2008-07-05 01:01:01');
      $this->preferences[] = array('name'=>'owner_email', 'type'=>'string', 'default'=>'none@none.none');

      $this->preferences[] = array('name'=>'rewrite', 'type'=>'yesno', 'default'=>'no');
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
      
      switch ($feature)
      {
//TODO: generalize
        case 'core': 
          $this->RelativeInclude('modules/core/module.inc');
          break;
        case 'database': 
          $this->RelativeInclude('modules/database/mysql/mysql.inc');
          $this->Database = new MysqlDatabase() ;
          break;
        case 'imageprocessing': 
          $this->RelativeInclude('modules/imageprocessing/gd.inc');
          $this->ImageProcessing = new GDImageProcessing() ;
          break;
        case 'security': 
          $this->RelativeInclude('modules/security/default/default.inc');
          $this->Security = new DefaultSecurity() ;
          break;
        case 'theme': 
          $this->RelativeInclude('modules/theme/'.$this->GetPref('theme').'/theme.inc');
          $themename = $this->GetPref('theme').'theme';
          $this->Theme = new $themename;
          break;
        case 'photostore':
          $this->RelativeInclude('modules/photostore/'.$this->GetPref('photostore').'/module.inc');
          $modulename = $this->GetPref('photostore').'photostore';
          $this->PhotoStore = new $modulename;
          break;
        default:
          $this->Error("Non-existant feature requested: $feature");
      }
      $this->loaded_features[$feature]='loaded';
    }

    function LoadPreferences()
    {
      $query = $this->Database->Select('preferences','*');
      $result = $query->FetchAssoc();

      if ($result['sitename']) // upgrade hack
      {
        foreach($result as $key => $value)
        {
          if (strpos($key, 'auth_') === 0)
            $this->userpreferences['DefaultSecurity'][$key] = $value;
          else
            $this->userpreferences['CameraLife'][$key] = $value;
        }
        $this->olddb = TRUE;
      } else {
        while ($result)
        {
          $this->userpreferences[$result['prefmodule']][$result['prefkey']] = $result['prefvalue'];
          $result = $query->FetchAssoc();
        }
      }
    }

    function SavePreferences()
    {
      // Upgrade hack
      if ($this->olddb == TRUE)
      {
        $sql = 'DROP TABLE `'.$this->Database->myPrefix.'preferences`';
        $this->Database->Query($sql)
          or $this->Error(mysql_error());
        $sql =  "CREATE TABLE `".$this->Database->myPrefix."preferences` (
                `prefmodule` varchar(64) NOT NULL default 'core',
                `prefkey` varchar(64) NOT NULL,
                `prefvalue` varchar(255) NOT NULL ,
                `prefdefault` varchar(255) NOT NULL,
                PRIMARY KEY (`prefmodule`, `prefkey`)
                );";
        $this->Database->Query($sql)
          or $this->Error(mysql_error());
      }

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

    function GetSmallIcon()
    {
      return array("name"=>$this->GetPref('siteabbr'),
                   "href"=>$this->base_url.'/index.php',
                   'image'=>'small-main',
                   'rel'=>'home');
    }

    function IconURL($name)
    {
      return $this->base_url."/modules/iconset/".$this->GetPref('iconset')."/$name";
    }

    function Error($message, $file='', $line='')
    {
      echo '<div style="margin: 3em; background: gray; border: 2px solid gray; padding:0.5em">';
      echo '  <div style="text-align:center; color: white; font-size:large">';
      echo '    Camera Life has encountered an error!</div>';
      echo "  <div style=\"background: #eee; padding: 1em;\">$message</div>\n";
      if ($file)
        echo "  <div style=\"background: #eee; padding: 1em;\">Error at: <i>$file</i></div>\n";
      if ($line)
        echo "  <div style=\"background: #eee; padding: 1em;\">Line: <i>$line</i></div>\n";
      echo "</div></div>\n";

      if (isset($cameralife->Security) && $cameralife->Security->authorize('admin_customize'))
      {
        echo "For details, view HTML source<!--\n";
        var_dump(debug_backtrace());
        echo "\n-->\n";
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
