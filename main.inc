<?php
#if ($_SERVER['REMOTE_ADDR'] != '71.225.133.122')
#die('site down 4 maintenance');

  // Is this the first run?
  if (file_exists('notinstalled.txt'))
  {
    header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/setup/');
    die('Redirecting to installer...');
  }

  class CameraLife
  {
    var $loaded_features;
    var $preferences;
    var $version;
    var $base_dir;
    var $base_url; // like http://camera.phor.net or http://phor.net/life/camera

    function CameraLife()
    {
      ini_set('magic_quotes_runtime', 0);
      ini_set('magic_quotes_sybase', 0);
      $this->base_dir = dirname(__FILE__);
      $this->base_url = 'http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']),'/');

      $this->version = '2.6.0b3';
      $this->RelativeInclude('modules/module.inc',1);

      // Load preferences
      $this->GetFeature('core');
      $this->GetFeature('database');
      $this->LoadPreferences();
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
    
      $this->RelativeInclude("modules/$feature/module.inc");
      $loaded_features[$feature]='loading';
      
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
          $this->RelativeInclude('modules/theme/'.$this->preferences['core']['theme'].'/theme.inc');
          $themename = $this->preferences['core']['theme'].'theme';
          $this->Theme = new $themename;
          break;
        default:
          $this->Error("Non-existant feature requested: $feature");
      }
      $this->loaded_features[$feature]='loaded';
    }

    function LoadPreferences()
    {
      $query = $this->Database->Select('preferences','*');
      $result = $query->FetchAssoc()
        or $this->Error('No site preferences can be found!');

      if ($result['sitename']) // old preferences db
      {
        foreach($result as $key => $value)
        {
          if (strpos($key, 'auth_') === 0)
            $this->preferences['defaultsecurity'][$key] = $value;
          else
            $this->preferences['core'][$key] = $value;
        }
        $this->preferences['olddb'] = TRUE;
      } else {
        while ($result)
        {
          $this->preferences[$result['prefmodule']][$result['prefkey']] = $result['prefvalue'];
          $this->preferences[$result['prefkey']] = $result['prefvalue'];
          $result = $query->FetchAssoc();
        }
      }

      $this->preferences['auth_cookie'] = 'cameralifeauth'; //TODO hmm
      $this->preferences['defaultsecurity']['auth_cookie'] = 'cameralifeauth'; //TODO hmm
    }

    function SavePreferences()
    {
      if ($this->preferences['olddb'] == TRUE)
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

      foreach($this->preferences as $module => $moduleprefs)
      {
        if (is_array($moduleprefs))
        {
          foreach($moduleprefs as $key => $value)
          {
            $values = array('prefmodule'=>$module, 'prefkey'=>$key, 'prefvalue'=>$value);
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
      return array("name"=>$this->preferences['core']['siteabbr'],
                   "href"=>'index.php',
                   'image'=>'small-main',
                   'rel'=>'home');
    }

    function Error($message, $file='', $line='')
    {
      echo '<div style="margin: 3em; background: gray; border: 2px solid gray; padding:0.5em">';
      echo '  <div style="text-align:center; color: white; font-size:large">';
      echo '    Camera Life has encountered an error!</div>';
      echo "  <div style=\"background: #eee; padding: 1em;\">$message</div>";
      if ($file)
        echo "  <div style=\"background: #eee; padding: 1em;\">Error at: <i>$file</i></div>";
      if ($line)
        echo "  <div style=\"background: #eee; padding: 1em;\">Line: <i>$line</i></div>";
      echo '</div></div>';
      exit(1);
    }
  }

  $cameralife = new CameraLife();

  if (count($GLOBALS['features']))
  foreach($features as $fcn)
    $cameralife->GetFeature($fcn);

#TODO: put somewhere else
/**
 *  db_log
 *
 *  Logs changes to the database. Information about the user is saved with information below
 *  this allows changes to be rolled back later.
 *
 *  @param $record_type - string - one of ('photo','album','preference','user')
 *  @param $record_id - int - id of the record being changed
 *  @param $value_field - string - field being changed
 *  @param $value_old - string - old field value
 *  @param $value_new - string - new field value
 *
 *  @return none
 */
  function db_log($record_type, $record_id, $value_field, $value_old, $value_new)
  {
    global $user, $_SERVER, $cameralife;
    if ($value_old==$value_new) return;

    $log['record_type'] = $record_type;
    $log['record_id'] = $record_id;
    $log['value_field'] = $value_field;
    $log['value_new'] = $value_new;
    $log['value_old'] = $value_old;
    $log['user_name'] = $cameralife->Security->GetName();
    $log['user_ip'] = $_SERVER['REMOTE_ADDR'];
    $log['user_date'] = date('Y-m-d');

    $cameralife->Database->Insert('logs',$log);
  }
?>
