<?php
/**
 * PDO implementation of the database class.
 * This requires some preferences to be set in $cameralife:
 * db_name db_user db_pass db_host and optionally db_prefix
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2014 Will Entriken
 * @access public
 */
class Database
{
  var $myConnection;
  var $myPrefix;
  var $myDBH;

  function Database()
  {
    global $cameralife, $db_host, $db_user, $db_pass, $db_name, $db_prefix;

    try 
    {
      $this->myDBH = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_pass);
      $this->myDBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(Exception $e)
    {
      $cameralife->Error('Database error: '.htmlentities($e->getMessage()));
    }
  }

  /** 
   * SELECT $selection FROM $table [WHERE $condition] [$extra]
   */
  function Select ($table, $selection='*', $condition='1', $extra='', $joins='', $bind=array())
  {
    global $cameralife;
    if (!$condition) $condition = '1';
    //TODO: security DO FURTHER table validation
    $tables = preg_split('/[, ]+/', $table);
    foreach ($tables as &$table) $table = $this->myPrefix.$table;
    $table = implode(',', $tables);
    $sql = "SELECT $selection FROM $table $joins WHERE $condition $extra";
    $stmt = NULL;
    try 
    {
      $stmt = $this->myDBH->prepare($sql);
      if (count($bind)) {
        foreach ($bind as $name=>$val)
          $stmt->bindValue(':'.$name, $val);
      }
      $stmt->execute();
    }
    catch(Exception $e)
    {
      $cameralife->Error('Database error: '.htmlentities($e->getMessage()));
    }
    return new PDOIterator($stmt);
  }
  
  /**
  * SELECT $selection FROM $table [WHERE $condition] [$extra]
  */
  function SelectOne ($table, $selection, $condition='1', $extra='', $joins='', $bind=array())
  {
    global $cameralife;
    if (!$condition) $condition = '1';
    //TODO: security DO FURTHER table validation
    $tables = preg_split('/[, ]+/', $table);
    foreach ($tables as &$table) $table = $this->myPrefix.$table;
    $table = implode(',', $tables);
    $sql = "SELECT $selection FROM $table $joins WHERE $condition $extra";
    $stmt = NULL;
    $result = NULL;
    try 
    {
      $stmt = $this->myDBH->prepare($sql);
      if (count($bind)) {
        foreach ($bind as $name=>$val)
          $stmt->bindValue(':'.$name, $val);
      }
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_NUM);
    }
    catch(Exception $e)
    {
      $cameralife->Error('Database error: '.htmlentities($e->getMessage()));
    }
    return $result[0];
  }
  
  function Update ($table, $values, $condition='1', $extra='')
  {
    global $cameralife;
    $setstring = '';
    foreach($values as $key => $value)
      $setstring .= "`$key` = ?, ";
    $setstring = substr($setstring, 0, -2); // chop off last ', '
    $sql = "UPDATE ".$this->myPrefix."$table SET $setstring WHERE $condition $extra";
    try 
    {
      $stmt = $this->myDBH->prepare($sql);
      $i = 1;
      foreach ($values as $val)
        $stmt->bindValue($i++, $val);
      $stmt->execute();
    }
    catch(Exception $e)
    {
      $cameralife->Error('Database error: '.htmlentities($e->getMessage()));
    }
    return $stmt->rowCount();
  }

  function Insert ($table, $values, $extra='')
  {
    global $cameralife;
    $setstring = '';
    foreach($values as $key => $value)
      $setstring .= "`$key` = ?, ";
    $setstring = substr($setstring, 0, -2); // chop off last ', '
    $sql = "INSERT INTO ".$this->myPrefix."$table SET $setstring $extra";
    try 
    {
      $stmt = $this->myDBH->prepare($sql);
      $i = 1;
      foreach ($values as $val)
        $stmt->bindValue($i++, $val);
      $stmt->execute();
    }
    catch(Exception $e)
    {
      $cameralife->Error('Database error: '.htmlentities($e->getMessage()));
    }
    return $this->myDBH->lastInsertId();
  }

  function Delete ($table, $condition='1', $extra='', $bind=array())
  {
    global $cameralife;
    $sql = "DELETE FROM ".$this->myPrefix."$table WHERE $condition $extra";
    try 
    {
      $stmt = $this->myDBH->prepare($sql);
      $i = 1;
      if (count($bind))
        foreach ($bind as $name=>$val)
          $stmt->bindValue(':'.$name, $val);
      $stmt->execute();
    }
    catch(Exception $e)
    {
      $cameralife->Error('Database error: '.htmlentities($e->getMessage()));
    }
    return $stmt->rowCount();
  }
}

/*
 * PDO implementation of the iterator class 
 */
class PDOIterator
{
  var $myResult;

  function PDOIterator($mysql_result)
  {
    $this->myResult = $mysql_result;
  }

  function FetchAssoc () 
  {
    return $this->myResult->fetch(PDO::FETCH_ASSOC);
  }
}


?>
