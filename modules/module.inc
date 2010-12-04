<?php
/** Base class for all modules -- Retrieving of preferences
 *@link http://fdcl.sourceforge.net
 *@version 2.6.3
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 */

  class CameraLifeModule
  {
    var $name;

    /**@var mixed $preferences 
     *
     * An array having arrays of:
     * <ul>
     *  <li> an named array with:</li>
     *  <li> 'name' => string</li>
     *  <li> 'desc' => string</li>
     *  <li> 'type' => 'directory', 'directoryrw' (must be read/writeable), 'string', 'number', 'yesno' ('0' or '1'), or an array of possible values (enum)</li>
     *  <li> 'default' => the default value</li>   
     * </ul>
     */
    var $preferences;

    function GetPref($key)
    {
      global $cameralife;

      if (isset($cameralife->userpreferences[get_class($this)][$key]))
        return $cameralife->userpreferences[get_class($this)][$key];

      foreach ($this->preferences as $pref)
        if($pref['name'] == $key) return $pref['default'];

      $cameralife->error('Unable to retreive preference: '. get_class($this) .' '.$key, __FILE__, __LINE__);
    }

    /**
     * Pass VALUE=NULL to reset to default
     */
    function SetPref($key, $value)
    {
      global $cameralife;

      if ($value == NULL)
      {
        foreach ($this->preferences as $pref)
          if($pref['name'] == $key) 
            $value = $pref['default'];
      }

      $cameralife->userpreferences[get_class($this)][$key] = $value;
      $cameralife->SavePreferences();
    }
  }

?>
