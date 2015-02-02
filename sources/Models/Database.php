<?php
namespace CameraLife\Models;

/**
 * PDO wrapper implementation of the database class
 *
 * @author    William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
 * @access    public
 */
class Database
{
    public static $dsn;
    
    public static $username;
    
    public static $password;
    
    public static $prefix;
    
    public static $schemaVersion;
    
    private static $pdoConnection;
    
    const REQUIRED_SCHEMA_VERSION = 5;

    /**
     * Creates static database connection using configuration from CONSTANTS
     * Yes, this should really be a factory singleton function... whatever
     *
     * @access private
     * @static
     * @return void
     */
    private static function connect()
    {
        if (!self::connectionParametersAreSet()) {
            throw new \Exception('Database credentials not defined');
        }
        self::$pdoConnection = new \PDO(self::$dsn, self::$username, self::$password);
        self::$pdoConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
    
    public static function installedSchemaIsCorrectVersion()
    {
        return self::$schemaVersion == self::REQUIRED_SCHEMA_VERSION;
    }

    public static function connectionParametersAreSet()
    {
        return isset(self::$dsn) && isset(self::$username) && isset(self::$password) && isset(self::$prefix);
    }

    public static function setupTables()
    {
        empty(self::$pdoConnection) && self::connect();
        $prefix = self::$prefix;

        $sql = 'SHOW TABLES LIKE "' . self::$prefix . '%"';
        $stmt = self::$pdoConnection->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount()) {
            throw new \Exception('The database already has tables in it, refusing to install again!');
        }
        
        $sql = "
              CREATE TABLE `{$prefix}albums` (
                `id` int(11) NOT NULL auto_increment,
                `topic` varchar(20) NOT NULL default '',
                `name` varchar(25) NOT NULL default '',
                `term` varchar(20) NOT NULL default '',
                `poster_id` int(11) NOT NULL default '0',
                `hits` bigint(20) NOT NULL default '0',
                PRIMARY KEY  (`id`)
              );";
        $stmt = self::$pdoConnection->prepare($sql);
        if (!$stmt->execute()) {
            throw new \Exception('Create albums table failed');
        }

        $sql = "
              CREATE TABLE `{$prefix}photos` (
                `id` int(11) NOT NULL auto_increment,
                `filename` varchar(255) NOT NULL default '',
                `path` varchar(255) NOT NULL default '',
                `description` varchar(255) NOT NULL default '',
                `keywords` varchar(255) NOT NULL default '',
                `username` varchar(30) default NULL,
                `status` int(11) NOT NULL default '0',
                `flag` enum('indecent','photography','subject','bracketing') default NULL,
                `width` int(11) default '0',
                `height` int(11) default '0',
                `tn_width` int(11) default '0',
                `tn_height` int(11) default '0',
                `hits` bigint(20) NOT NULL default '0',
                `created` date default NULL,
                `fsize` bigint(20) NOT NULL default '0',
                `mtime` bigint(20) NOT NULL default '0',
                `modified` int(1) NOT NULL default '0',
                PRIMARY KEY  (`id`),
                UNIQUE KEY `path` (`path`, `filename`)
              );";
        $stmt = self::$pdoConnection->prepare($sql);
        if (!$stmt->execute()) {
            throw new \Exception('Create photos table failed');
        }
        
        $sql = "
              CREATE TABLE `{$prefix}ratings` (
                `id` int(11) NOT NULL,
                `username` varchar(30) default NULL,
                `user_ip` varchar(16) NOT NULL,
                `rating` int(11) NOT NULL,
                `date` datetime NOT NULL,
                UNIQUE KEY `id_3` (`id`,`username`,`user_ip`),
                KEY `id` (`id`),
                KEY `id_2` (`id`,`username`,`user_ip`),
                KEY `id_4` (`id`)
              );";
        $stmt = self::$pdoConnection->prepare($sql);
        if (!$stmt->execute()) {
            throw new \Exception('Create ratings table failed');
        }
        
        $sql = "
              CREATE TABLE `{$prefix}comments` (
                `id` int(11) NOT NULL auto_increment,
                `photo_id` int(11) NOT NULL,
                `username` varchar(30) NOT NULL,
                `user_ip` varchar(16) NOT NULL,
                `comment` varchar(255) NOT NULL,
                `date` datetime NOT NULL,
                PRIMARY KEY  (`id`),
                KEY `id` (`photo_id`)
              );";
        $stmt = self::$pdoConnection->prepare($sql);
        if (!$stmt->execute()) {
            throw new \Exception('Create comments table failed');
        }
        
        $sql = "
              CREATE TABLE `{$prefix}preferences` (
                `prefmodule` varchar(64) NOT NULL default 'core',
                `prefkey` varchar(64) NOT NULL default '',
                `prefvalue` varchar(255) NOT NULL default '',
                `prefdefault` varchar(255) NOT NULL default '',
                PRIMARY KEY  (`prefmodule`,`prefkey`)
              );";
        $stmt = self::$pdoConnection->prepare($sql);
        if (!$stmt->execute()) {
            throw new \Exception('Create preferences table failed');
        }
        
        $sql = "
              CREATE TABLE `{$prefix}users` (
                `id` int(10) NOT NULL auto_increment,
                `username` varchar(30) NOT NULL default '',
                `password` varchar(255) NOT NULL default '',
                `auth` int(11) NOT NULL default '0',
                `cookie` varchar(64) NOT NULL default '',
                `last_online` date NOT NULL default '0000-00-00',
                `last_ip` varchar(20) default NULL,
                `email` varchar(80) default NULL,
                PRIMARY KEY  (`username`),
                UNIQUE KEY `username` (`username`),
                UNIQUE KEY `id` (`id`)
              );";
        $stmt = self::$pdoConnection->prepare($sql);
        if (!$stmt->execute()) {
            throw new \Exception('Create users table failed');
        }
        
        $sql = "
              CREATE TABLE `{$prefix}exif` (
                `photoid` int(11) NOT NULL,
                `tag` varchar(50) NOT NULL,
                `value` varchar(255) NOT NULL,
                PRIMARY KEY  (`photoid`,`tag`),
                KEY `photoid` (`photoid`)
              );";
        $stmt = self::$pdoConnection->prepare($sql);
        if (!$stmt->execute()) {
            throw new \Exception('Create exif table failed');
        }
        
        $sql = "
              CREATE TABLE `{$prefix}logs` (
                `id` int(11) NOT NULL auto_increment,
                `record_type` enum('album','photo','preference','user') NOT NULL default 'album',
                `record_id` int(11) NOT NULL default '0',
                `value_field` varchar(40) NOT NULL default '',
                `value_new` text NOT NULL,
                `user_name` varchar(30) NOT NULL default '',
                `user_ip` varchar(16) NOT NULL default '',
                `user_date` date NOT NULL default '0000-00-00',
                PRIMARY KEY  (`id`)
              );";
        $stmt = self::$pdoConnection->prepare($sql);
        if (!$stmt->execute()) {
            throw new \Exception('Create logs table failed');
        }
    }


    /**
     * SELECT $selection FROM $table [WHERE $condition] [$extra]
     *
     * @access public
     * @param  mixed  $table
     * @param  string $selection (default: '*')
     * @param  string $condition (default: '1')
     * @param  string $extra     (default: '')
     * @param  string $joins     (default: '')
     * @param  array  $bind      (default: array())
     * @return void
     */
    public static function select($table, $selection = '*', $condition = '1', $extra = '', $joins = '', $bind = array())
    {
        empty(self::$pdoConnection) && self::connect();
        if (!$condition) {
            $condition = '1';
        }
        $tables = preg_split('/[, ]+/', $table);
        foreach ($tables as &$table) {
            $table = self::$prefix . $table;
        }
        $table = implode(',', $tables);
        $sql = "SELECT $selection FROM $table $joins WHERE $condition $extra";
        $stmt = null;
        $stmt = self::$pdoConnection->prepare($sql);
        if (count($bind)) {
            foreach ($bind as $name => $val) {
                if (stristr($condition, ':' . $name)) {
                    $stmt->bindValue(':' . $name, $val);
                }
            }
        }
        $stmt->execute();
        return new DatabaseIterator($stmt);
    }

    /**
     * SELECT $selection FROM $table [WHERE $condition] [$extra]
     *
     * @access public
     * @param  mixed  $table
     * @param  mixed  $selection
     * @param  string $condition (default: '1')
     * @param  string $extra     (default: '')
     * @param  string $joins     (default: '')
     * @param  array  $bind      (default: array())
     * @return void
     */
    public static function selectOne($table, $selection = '*', $condition = '1', $extra = '', $joins = '', $bind = array())
    {
        $selection = self::select($table, $selection, $condition, $extra, $joins, $bind);
        $first = $selection->fetchAssoc();
        return current($first);
    }

    public static function update($table, $values, $condition = '1', $extra = '')
    {
        empty(self::$pdoConnection) && self::connect();
        $setstring = '';
        foreach (array_keys($values) as $key) {
            $setstring .= "`$key` = ?, ";
        }
        $setstring = substr($setstring, 0, -2); // chop off last ', '
        $sql = "UPDATE " . self::$prefix . "$table SET $setstring WHERE $condition $extra";
        $stmt = self::$pdoConnection->prepare($sql);
        foreach ($values as $idx => $val) {
            $stmt->bindValue($idx + 1, $val);
        }
        $stmt->execute();
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
    public static function insert($table, $values)
    {
        empty(self::$pdoConnection) && self::connect();
        $columns = '`' . implode('`,`', array_keys($values)) . '`';
        $value_expr = ':' . implode(array_keys(array_values($values)), ', :');
        $sql = "INSERT INTO " . self::$prefix . "$table ($columns) VALUES ($value_expr)";
        $stmt = self::$pdoConnection->prepare($sql);
        foreach (array_values($values) as $name => $val) {
            $stmt->bindValue(':' . $name, $val);
        }
        $stmt->execute();
        return self::$pdoConnection->lastInsertId();
    }

    public static function delete($table, $condition = '1', $extra = '', $bind = array())
    {
        empty(self::$pdoConnection) && self::connect();
        $sql = "DELETE FROM " . self::$prefix . "$table WHERE $condition $extra";
        $stmt = self::$pdoConnection->prepare($sql);
        if (count($bind)) {
            foreach ($bind as $name => $val) {
                $stmt->bindValue(':' . $name, $val);
            }
        }
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Run an arbitrary SQL statement against the database, all tables must be prefixed already
     *
     * @access public
     * @static
     * @param  string $sql
     * @param  array  $bind (default: array())
     * @return void
     */
    public static function run($sql, $bind = array())
    {
        empty(self::$pdoConnection) && self::connect();
        $bindWithColons = array();
        foreach ($bind as $name => $val) {
            $bindWithColons[':' . $name] = $val;
        }
        $stmt = self::$pdoConnection->prepare($sql);
        return $stmt->execute($bindWithColons);
    }
}
