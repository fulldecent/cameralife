<?php
require_once dirname(__FILE__) . '/../modules/core/database.php';

/**
* http://blogs.kent.ac.uk/webdev/2011/07/14/phpunit-and-unserialized-pdo-instances/
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/

// stub implementation
class StubCameraLife
{
    function error($message)
    {
        die('CAMERA LIFE ERROR: ' . $message);
    }
}

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    public function testDatabase()
    {
        $stubCameraLife = new StubCameraLife();
        $dsn = 'sqlite:' . dirname(__FILE__) . '/test.sqlite3';
        $database = new CameraLife\Database($stubCameraLife, $dsn);
    }

    public function testDatabaseSelect()
    {
        $stubCameraLife = new StubCameraLife();
        $dsn = 'sqlite:' . dirname(__FILE__) . '/test.sqlite3';
        $database = new CameraLife\Database($stubCameraLife, $dsn);
        $query = $database->select('photos', 'count(*) as A');
        $result = $query->fetchAssoc();
        $this->assertEquals($result['A'], 0);
    }

    public function testDatabaseSelectOne()
    {
        $stubCameraLife = new StubCameraLife();
        $dsn = 'sqlite:' . dirname(__FILE__) . '/test.sqlite3';
        $database = new CameraLife\Database($stubCameraLife, $dsn);
        $resultOne = $database->selectOne('photos', 'count(*) as A');
        $this->assertEquals($resultOne, 0);
    }

    public function testDatabaseUpdate()
    {
        $stubCameraLife = new StubCameraLife();
        $dsn = 'sqlite:' . dirname(__FILE__) . '/test.sqlite3';
        $database = new CameraLife\Database($stubCameraLife, $dsn);
        $result = $database->update('preferences', array('prefvalue'=>date('Y-m-d')), "prefkey='sitedate'");
        $this->assertEquals($result, 1);
    }

    public function testDatabaseInsertDelete()
    {
        $stubCameraLife = new StubCameraLife();
        $dsn = 'sqlite:' . dirname(__FILE__) . '/test.sqlite3';
        $database = new CameraLife\Database($stubCameraLife, $dsn);
        $newRecord = array(
            'prefmodule' => 'TESTMODULE',
            'prefkey' => 'TESTKEY',
            'prefvalue' => 'TESTVALUE',
            'prefdefault' => 'TESTDEFAULT'
        );

        $deletedCount = $database->delete('preferences', "prefmodule='TESTMODULE'");

        $lastInsertID = $database->insert('preferences', $newRecord);
        $this->assertTrue($lastInsertID > 0);

        $deletedCount = $database->delete('preferences', "prefmodule='TESTMODULE'");
        $this->assertTrue($deletedCount == 1);
    }
}
