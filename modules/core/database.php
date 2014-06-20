<?php
namespace CameraLife;

/**
 * PDO wrapper implementation of the database class
 *
 * @author    William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
 * @access    public
 */
class Database
{
    public $myConnection;
    public $myPrefix;
    public $myDBH;
    private $cameralife;

    public function __construct($cameralife, $db_dsn, $db_user = '', $db_pass = '', $db_prefix = '')
    {
        $this->cameralife = $cameralife;
        $this->myPrefix = $db_prefix;
        try {
            $this->myDBH = new \PDO($db_dsn, $db_user, $db_pass);
            $this->myDBH->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            $this->cameralife->error('Database error: ' . htmlentities($e->getMessage()));
        }
    }

    /**
     * SELECT $selection FROM $table [WHERE $condition] [$extra]
     *
     * @access public
     * @param mixed $table
     * @param string $selection (default: '*')
     * @param string $condition (default: '1')
     * @param string $extra (default: '')
     * @param string $joins (default: '')
     * @param array $bind (default: array())
     * @return void
     */
    public function select($table, $selection = '*', $condition = '1', $extra = '', $joins = '', $bind = array())
    {
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
                    if (stristr($condition, ':' . $name)) {
                        $stmt->bindValue(':' . $name, $val);
                    }
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            $this->cameralife->error('Database error: ' . htmlentities($e->getMessage()));
        }

        return new PDOIterator($stmt);
    }

    /**
     * SELECT $selection FROM $table [WHERE $condition] [$extra]
     *
     * @access public
     * @param mixed $table
     * @param mixed $selection
     * @param string $condition (default: '1')
     * @param string $extra (default: '')
     * @param string $joins (default: '')
     * @param array $bind (default: array())
     * @return void
     */
    public function selectOne($table, $selection, $condition = '1', $extra = '', $joins = '', $bind = array())
    {
        $cameralife = $this->cameralife;
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
                    if (stristr($condition, ':' . $name)) {
                        $stmt->bindValue(':' . $name, $val);
                    }
                }
            }
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_NUM);
        } catch (Exception $e) {
            $cameralife->error('Database error: ' . htmlentities($e->getMessage()));
        }

        return $result[0];
    }

    public function update($table, $values, $condition = '1', $extra = '')
    {
        $cameralife = $this->cameralife;
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

    /**
     * insert function.
     *
     * @access public
     * @param  string $table
     * @param  array  $values
     * @param  string $extra  (default: '')
     * @return integer
     */
    public function insert($table, $values, $extra = '')
    {
        $cameralife = $this->cameralife;
        $columns = '`' . implode('`,`', array_keys($values)) . '`';
        $value_expr = ':' . implode(array_keys(array_values($values)), ', :');
        $sql = "INSERT INTO " . $this->myPrefix . "$table ($columns) VALUES ($value_expr)";
        try {
            $stmt = $this->myDBH->prepare($sql);
            foreach (array_values($values) as $name => $val) {
                $stmt->bindValue(':' . $name, $val);
            }
            $stmt->execute();
        } catch (Exception $e) {
            $cameralife->error('Database error: ' . htmlentities($e->getMessage()));
        }

        return $this->myDBH->lastInsertId();
    }

    public function delete($table, $condition = '1', $extra = '', $bind = array())
    {
        $cameralife = $this->cameralife;
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
    private $myResult;

    public function __construct($mysqlResult)
    {
        $this->myResult = $mysqlResult;
    }

    public function fetchAssoc()
    {
        return $this->myResult->fetch(\PDO::FETCH_ASSOC);
    }
}
