<?php
require_once dirname(__FILE__) . '/../main.inc';
require_once dirname(__FILE__) . '/../modules/core/stats.class.php';

/**
* http://blogs.kent.ac.uk/webdev/2011/07/14/phpunit-and-unserialized-pdo-instances/
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/

class StatsTest extends PHPUnit_Framework_TestCase
{
    public function testStats()
    {
        $features = array();
        $dsn = 'sqlite:' . dirname(__FILE__) . '/test.sqlite3';
        $cameralife = CameraLife\CameraLife::cameraLifeWithFeaturesAndTestDSN($features, $dsn);
        $stats = new CameraLife\Stats($cameralife);
    }

    public function testStatsGetCounts()
    {
        $features = array();
        $dsn = 'sqlite:' . dirname(__FILE__) . '/test.sqlite3';
        $cameralife = CameraLife\CameraLife::cameraLifeWithFeaturesAndTestDSN($features, $dsn);
        $stats = new CameraLife\Stats($cameralife);
        $counts = $stats->getCounts();
        $this->assertEquals(count($counts), 9);
    }

    public function testStatsGetPopularPhotos()
    {
        $features = array();
        $dsn = 'sqlite:' . dirname(__FILE__) . '/test.sqlite3';
        $cameralife = CameraLife\CameraLife::cameraLifeWithFeaturesAndTestDSN($features, $dsn);
        $stats = new CameraLife\Stats($cameralife);
        $popular = $stats->getPopularPhotos();
        $this->assertTrue(is_array($popular));
    }

    public function testStatsGetPopularAlbums()
    {
        $features = array();
        $dsn = 'sqlite:' . dirname(__FILE__) . '/test.sqlite3';
        $cameralife = CameraLife\CameraLife::cameraLifeWithFeaturesAndTestDSN($features, $dsn);
        $stats = new CameraLife\Stats($cameralife);
        $popular = $stats->getPopularAlbums();
        $this->assertTrue(is_array($popular));
    }

    public function testStatsGetFunFacts()
    {
        $features = array();
        $dsn = 'sqlite:' . dirname(__FILE__) . '/test.sqlite3';
        $cameralife = CameraLife\CameraLife::cameraLifeWithFeaturesAndTestDSN($features, $dsn);
        $stats = new CameraLife\Stats($cameralife);
        $facts = $stats->getFunFacts();
        $this->assertTrue(count($facts) > 0);
    }
}
