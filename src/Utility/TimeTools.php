<?php namespace FHTeam\LaravelRedisCache\Utility;

use Carbon\Carbon;
use DateTime;
use Exception;

/**
 * Class Tools
 *
 * @package FHTeam\LaravelRedisCache\Utility
 */
class TimeTools
{
    /**
     * Calculate the number of minutes with the given duration.
     *
     * @param  DateTime|int $minutes Either the exact date at which the item expire, or ttl in minutes
     *
     * @return int A number of seconds to use in Redis commands
     * @throws Exception
     */
    public static function getTtlInSeconds($minutes)
    {
        if ($minutes instanceof DateTime) {
            $fromNow = -Carbon::instance($minutes)->diffInSeconds(null, false);
            if ($fromNow < 0) {
                throw new Exception("Cache TTL should be >=0");
            }

            return $fromNow;
        }

        return $minutes * 60;
    }
}
