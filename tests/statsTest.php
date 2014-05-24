<?php

require_once dirname(__FILE__) . '/../modules/core/stats.class.php';

class MoneyTest extends PHPUnit_Framework_TestCase
{
    public function testDumbTest()
    {
        $this->assertEquals(1,1);
    }
    public function testDumbTest2()
    {
        $stats = new Stats();
        $this->assertEquals(1,1);
    }
}
