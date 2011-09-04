<?php
/**
 *MYSQL implementation of the database class.
 * This requires some preferences to be set in $cameralife:
 * db_name db_user db_pass db_host and optionally db_prefix
 *@version 
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 *@link http://fdcl.sourceforge.net
 */
  class MysqlDatabase extends DatabaseModule
  {
    var $myConnection;
    var $myPrefix;
/**
*@todo A better way to pass the variables- $db_host, $db_user, $db_pass, $db_name, $db_prefix;

*/
    function MySqlDatabase ()
    {
      global $cameralife, $db_host, $db_user, $db_pass, $db_name, $db_prefix;

      //TODO: find a better way to pass in these variables
      $this->myConnection = mysql_connect($db_host, $db_user, $db_pass)
        or die(mysql_error());
      mysql_select_db($db_name, $this->myConnection)
        or die(mysql_error());
      $this->myPrefix = $db_prefix;
    }

    /** SELECT $selection FROM $table [WHERE $condition] [$extra]
    *
    */
    function Select ($table, $selection='*', $condition='1', $extra='') 
    {
      if ($condition == '') $condition = '1';

      $tables = preg_split('/[, ]+/', $table);
      #foreach ($tables as &$table) $table = $this->myPrefix.$table; #PHP 5 only
      foreach ($tables as $key=>$table) $tables[$key] = $this->myPrefix.trim($table);
      $table = implode(', ', $tables);

      $sql = "SELECT $selection FROM $table WHERE $condition $extra";

      $query = mysql_query($sql, $this->myConnection)
        or die (mysql_error().'<pre>'.$sql.'</pre>');
      return new MysqlIterator($query, $sql);
    }
    
    /**
    *SELECT $selection FROM $table [WHERE $condition] [$extra]
    */
    function SelectOne ($table, $selection, $condition='1', $extra='') 
    {
      if (strlen($condition) == 0) $condition = '1';

      $sql = "SELECT $selection FROM ".$this->myPrefix."$table WHERE $condition $extra";

      $query = mysql_query($sql, $this->myConnection)
        or die (mysql_error().' SQL: '.$sql);
      return mysql_result($query, 0);
    }
    
    function Update ($table, $values, $condition='1', $extra='')
    {
      $setstring = '';
      foreach($values as $key => $value)
      {
        if (is_string($value))
          $escapedvalue = "'".mysql_real_escape_string($value)."'";
        elseif (is_float($value) || is_int($value))
          $escapedvalue = $value;
        elseif (is_null($value) || is_bool($value) && !$value)
          continue;
        else
          die("Invalid type in MYSQL::Update $key=>$value");

        $setstring .= "$key = $escapedvalue, ";
      }

      $setstring = substr($setstring, 0, -2); // chop off last ', '
      $sql = "UPDATE ".$this->myPrefix."$table SET $setstring WHERE $condition $extra";
      $query = mysql_query($sql, $this->myConnection)
        or die (mysql_error().' SQL: <pre>'.$sql.'</pre>');

      return mysql_affected_rows();
    }

    function Insert ($table, $values, $extra='')
    {
      foreach($values as $key => $value)
      {
        if (is_string($value))
          $escapedvalue = "'".mysql_real_escape_string($value)."'";
        elseif (is_float($value) || is_int($value))
          $escapedvalue = $value;
        elseif (is_null($value) || is_bool($value) && !$value)
          continue;
        else
          die("Invalid type in MYSQL::Insert $key=>$value");

        $setstring .= "$key = $escapedvalue, ";
      }

      $setstring = substr($setstring, 0, -2); // chop off last ', '
      $sql = "INSERT INTO ".$this->myPrefix."$table SET $setstring $extra";

      $query = mysql_query($sql, $this->myConnection)
        or die (mysql_error()."<br>\nSQL: ".$sql);

      $rows = mysql_affected_rows();
#      if ($rows == 0)
#        die ("No rows affected in Mysql::Insert, SQL:<b>$sql</b>");
      return mysql_insert_id($this->myConnection);
    }

    function Delete ($table, $condition='1', $extra='')
    {
      $sql = "DELETE FROM ".$this->myPrefix."$table WHERE $condition $extra";

      $query = mysql_query($sql, $this->myConnection)
        or die (mysql_error().' SQL: '.$sql);

      $rows = mysql_affected_rows();
      return $rows;
    }

    function Query ($sql)
    {
      global $cameralife;

      $query = mysql_query($sql, $this->myConnection)
        or $cameralife->Error('Database error: '.mysql_error()."<p>$sql</p>");
      if ($query === TRUE)
        return TRUE;
      else if ($query === FALSE)
        return FALSE;
      else 
        return new MysqlIterator($query);
    }

  }
/**MYSQL implementation of the iterator class 
*/
  class MysqlIterator extends DatabaseIterator
  {
    var $myResult;
    var $mySQL;

    function MysqlIterator($mysql_result, $SQL='')
    {
      $this->myResult = $mysql_result;
      $this->mySQL = $SQL;
    }

    function FetchAssoc () 
    {
      return mysql_fetch_assoc($this->myResult);
    }
  }


?>
