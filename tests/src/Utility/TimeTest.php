<?php namespace FHTeam\LaravelRedisCache\Tests\Utility;

use Carbon\Carbon;
use Exception;
use FHTeam\LaravelRedisCache\Tests\TestBase;
use FHTeam\LaravelRedisCache\Utility\TimeTools;

class TimeTest extends TestBase
{
    public function testGetTtlInSeconds()
    {
        $this->assertEquals(3600, TimeTools::getTtlInSeconds(60));

        $now = Carbon::now();
        $expire = $now->addMinute();
        $this->assertEquals(60, TimeTools::getTtlInSeconds($expire));
    }

    public function testGetTtlInSecondsException()
    {
        $now = Carbon::now();
        $this->setExpectedException(Exception::class);
        TimeTools::getTtlInSeconds($now->subMinute(2));
        $this->setExpectedException(Exception::class);
    }
}
