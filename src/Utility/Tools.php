<?php

namespace FHTeam\LaravelRedisCache\Utility;

use Carbon\Carbon;
use DateTime;
use Exception;

/**
 * Class Tools
 *
 * @package FHTeam\LaravelRedisCache\Utility
 */
class Tools
{
    /**
     * Calculate the number of minutes with the given duration.
     *
     * @param  \DateTime|int $minutes Either the exact date at which the item expire, or ttl in minutes
     *
     * @return int A number of seconds to use in Redis commands
     * @throws Exception
     */
    public static function getTtlInSeconds($minutes)
    {
        if ($minutes instanceof DateTime) {
            $fromNow = Carbon::instance($minutes)->diffInSeconds();
            if ($fromNow < 0) {
                throw new Exception("Cache TTL should be >=0");
            }
            return $fromNow;
        }

        return $minutes * 60;
    }

    /**
     * @param string   $prefix
     * @param string[] $keys
     *
     * @return array
     */
    public static function addPrefixToArrayValues($prefix, array $keys)
    {
        $key = array_map(function ($value) use ($prefix) {
            return $prefix . $value;
        }, $keys);

        return $key;
    }

    public static function addPrefixToArrayKeys($prefix, array $array)
    {
        $result = [];
        foreach ($array as $key => $val) {
            $result[$prefix . $key] = $val;
        }

        return $result;
    }

    public static function stripPrefixFromArrayKeys($prefix, array $array)
    {
        $result = [];
        $posKeyStart = strlen($prefix);

        foreach ($array as $key => $val) {
            $result[substr($key, $posKeyStart)] = $val;
        }

        return $result;
    }
}
