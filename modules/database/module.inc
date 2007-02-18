<?php
 /* Base class for all database modules
  */

  class DatabaseModule extends CameraLifeModule
  {
    /**
     *  Initializing a database module connects to the database
     */
    function DatabaseModule ($username, $password, $server, $db, $prefix) { }

    /* Return an iterator of the result of a select statement */
    /* SELECT $selection FROM $table [WHERE $condition] [$extra] */
    function Select ($table, $selection, $condition, $extra) { }

    /* Return the first column/row of  a select statement
       SELECT $selection FROM $table [WHERE $condition] [$extra] */
    function SelectOne ($table, $selection, $condition='1', $extra='') { }

    /* Update record(s) in the database
       if $selection is a string, it is treated as a where clause,
       if $selection is an associative array, it is treated as WHERE $KEY = $VALUE
         $VALUE will be handles correctly if it is a int of string 
         e.g., a string will get quoted and escaped 

       $value is an associateive array
       Returns the number of rows affected
    */
    function Update ($table, $value, $condition, $extra='') { }
  }

  class DatabaseIterator
  {
    function FetchAssoc () { }
  }


?>
