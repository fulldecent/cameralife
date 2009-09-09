<?php
 /** Retrieving of preferences
 *@link http://fdcl.sourceforge.net
  *@version 2.6.3b3
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */
  /**Base class for all modules
  */

  class CameraLifeModule
  {
    var $name;

    /**@var mixed $preferences 
     *
     * in the format:
     * <ul>
     *<li>  an array of:</li>
     *<li> an named array with:</li>
     *<li>  name -- string</li>
     *<li> desc -- string</li>
     *<li>  type -- directory, string, number, yesno ('0' or '1'), or an array of possible values (enum)</li>
     *<li>   default -- the default value</li>   
     *</ul>
     */
    var $preferences;

    function GetPref($key)
    {
      global $cameralife;

      $set = $cameralife->userpreferences[get_class($this)][$key];
      if (isset($set)) return $set;

      foreach ($this->preferences as $pref)
        if($pref['name'] == $key) return $pref['default'];

      $cameralife->error('Unable to retreive preference: '. get_class($this) .' '.$key, __FILE__, __LINE__);
    }
/**
*NULL value gets reset to default
*/
// NULL value resets to the default
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
