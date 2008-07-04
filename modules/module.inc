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
  }

?>
