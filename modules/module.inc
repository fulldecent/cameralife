<?php
 /* Base class for all modules
  */

  class CameraLifeModule
  {
    var $name;

    /**
     * in the format:
     *   an array of:
     *     an named array with:
     *       name -- string
     *       desc -- string
     *       type -- directory, string, number, yesno (0/1), or an array of possible values (enum)
     *       default -- the default value
     *
     */
    var $preferences;

    function GetPref($key)
    {
      global $cameralife;

      $set = $cameralife->userpreferences[get_class($this)][$key];
      if ($set) return $set;

      foreach ($this->preferences as $pref)
        if($pref['name'] == $key) return $pref['default'];

      $cameralife->error('Unable to retreive preference: '. get_class($this) .' '.$key, __FILE__, __LINE__);
    }

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
