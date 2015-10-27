<?php namespace FHTeam\LaravelRedisCache\Tests\Utility;

use Carbon\Carbon;
use DateTime;
use Exception;
use FHTeam\LaravelRedisCache\Tests\TestBase;
use FHTeam\LaravelRedisCache\Utility\TimeTools;

class TimeTest extends TestBase
{
    public function testGetTtlInSeconds()
    {
        $this->assertEquals(3600, TimeTools::getTtlInSeconds(60));

        $now = new DateTime("2012-07-08 11:14:15.638276");
        $expire = new DateTime("2012-07-08 11:15:15.889342");

        $this->assertEquals(60, TimeTools::getTtlInSeconds($expire, Carbon::instance($now)));

        $this->setExpectedException(Exception::class);
        TimeTools::getTtlInSeconds($now, Carbon::instance($expire));
    }
}
