<?php
/**
*
*@version 2.6.3
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  *@link http://fdcl.sourceforge.net
*/
 /** Base class for all database modules
     */

  class DatabaseModule extends CameraLifeModule
  {
    /**
     *  Initializing a database module connects it to the database
     */
    function DatabaseModule ($username, $password, $server, $db, $prefix) { }

    /**
    *Return an iterator of the result of a select statement 
    * SELECT $selection FROM $table [WHERE $condition] [$extra] 
    */
    function Select ($table, $selection, $condition, $extra) { }

    /**
    *Return the first column/row of  a select statement
      * SELECT $selection FROM $table [WHERE $condition] [$extra]
      */
    function SelectOne ($table, $selection, $condition='1', $extra='') { }

    /** Update record(s) in the database
    *    
     *if $selection is a string, it is treated as a where clause,
       *if $selection is an associative array, it is treated as WHERE $KEY = $VALUE. 
       *  $VALUE will be handled correctly if it is a int of string 
        * e.g., a string will get quoted and escaped 
      * @param mixed $value is an associative array,returns the number of rows affected
    */
    function Update ($table, $value, $condition, $extra='') { }
  }
/**Base class for all database iterators
*/
  class DatabaseIterator
  {
    function FetchAssoc () { }
  }


?>
