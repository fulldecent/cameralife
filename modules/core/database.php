<?php

/**
 * PDO implementation of the database class.
 * This requires some preferences to be set in $cameralife:
 * db_name db_user db_pass db_host and optionally db_prefix
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2014 William Entriken
 * @access public
 */
class Database
{
    public $myConnection;
    public $myPrefix;
    public $myDBH;

    public function __construct()
    {
//TODO don't use global here
        global $cameralife, $db_host, $db_user, $db_pass, $db_name, $db_prefix;

        try {
            $this->myDBH = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_pass);
            $this->myDBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            $cameralife->error('Database error: ' . htmlentities($e->getMessage()));
        }
    }

    /**
     * SELECT $selection FROM $table [WHERE $condition] [$extra]
     */
    public function select($table, $selection = '*', $condition = '1', $extra = '', $joins = '', $bind = array())
    {
        global $cameralife;
        if (!$condition) {
            $condition = '1';
        }
        //TODO: security DO FURTHER table validation
        $tables = preg_split('/[, ]+/', $table);
        foreach ($tables as &$table) {
            $table = $this->myPrefix . $table;
        }
        $table = implode(',', $tables);
        $sql = "SELECT $selection FROM $table $joins WHERE $condition $extra";
        $stmt = null;
        try {
            $stmt = $this->myDBH->prepare($sql);
            if (count($bind)) {
                foreach ($bind as $name => $val) {
                    $stmt->bindValue(':' . $name, $val);
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            $cameralife->error('Database error: ' . htmlentities($e->getMessage()));
        }

        return new PDOIterator($stmt);
    }

    /**
     * SELECT $selection FROM $table [WHERE $condition] [$extra]
     */
    public function selectOne($table, $selection, $condition = '1', $extra = '', $joins = '', $bind = array())
    {
        global $cameralife;
        if (!$condition) {
            $condition = '1';
        }
        //TODO: security DO FURTHER table validation
        $tables = preg_split('/[, ]+/', $table);
        foreach ($tables as &$table) {
            $table = $this->myPrefix . $table;
        }
        $table = implode(',', $tables);
        $sql = "SELECT $selection FROM $table $joins WHERE $condition $extra";
        $stmt = null;
        $result = null;
        try {
            $stmt = $this->myDBH->prepare($sql);
            if (count($bind)) {
                foreach ($bind as $name => $val) {
                    $stmt->bindValue(':' . $name, $val);
                }
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_NUM);
        } catch (Exception $e) {
            $cameralife->error('Database error: ' . htmlentities($e->getMessage()));
        }

        return $result[0];
    }

    public function update($table, $values, $condition = '1', $extra = '')
    {
        global $cameralife;
        $setstring = '';
        foreach ($values as $key => $value) {
            $setstring .= "`$key` = ?, ";
        }
        $setstring = substr($setstring, 0, -2); // chop off last ', '
        $sql = "UPDATE " . $this->myPrefix . "$table SET $setstring WHERE $condition $extra";
        try {
            $stmt = $this->myDBH->prepare($sql);
            $i = 1;
            foreach ($values as $val) {
                $stmt->bindValue($i++, $val);
            }
            $stmt->execute();
        } catch (Exception $e) {
            $cameralife->error('Database error: ' . htmlentities($e->getMessage()));
        }

        return $stmt->rowCount();
    }

    public function insert($table, $values, $extra = '')
    {
        global $cameralife;
        $setstring = '';
        foreach ($values as $key => $value) {
            $setstring .= "`$key` = ?, ";
        }
        $setstring = substr($setstring, 0, -2); // chop off last ', '
        $sql = "INSERT INTO " . $this->myPrefix . "$table SET $setstring $extra";
        try {
            $stmt = $this->myDBH->prepare($sql);
            $i = 1;
            foreach ($values as $val) {
                $stmt->bindValue($i++, $val);
            }
            $stmt->execute();
        } catch (Exception $e) {
            $cameralife->error('Database error: ' . htmlentities($e->getMessage()));
        }

        return $this->myDBH->lastInsertId();
    }

    public function delete($table, $condition = '1', $extra = '', $bind = array())
    {
        global $cameralife;
        $sql = "DELETE FROM " . $this->myPrefix . "$table WHERE $condition $extra";
        try {
            $stmt = $this->myDBH->prepare($sql);
            if (count($bind)) {
                foreach ($bind as $name => $val) {
                    $stmt->bindValue(':' . $name, $val);
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            $cameralife->error('Database error: ' . htmlentities($e->getMessage()));
        }

        return $stmt->rowCount();
    }
}

/*
 * PDO implementation of the iterator class
 */

class PDOIterator
{
    public $myResult;

    public function PDOIterator($mysql_result)
    {
        $this->myResult = $mysql_result;
    }

    public function fetchAssoc()
    {
        return $this->myResult->fetch(PDO::FETCH_ASSOC);
    }
}
