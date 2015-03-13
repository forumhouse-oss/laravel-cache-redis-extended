<?php

namespace FHTeam\LaravelRedisCache\Utility;

/**
 * Low-level array tools
 *
 * @package FHTeam\LaravelRedisCache\Utility
 */
class ArrayTools
{
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

    /**
     * @param string $prefix
     * @param array  $array
     *
     * @return array
     */
    public static function addPrefixToArrayKeys($prefix, array $array)
    {
        $result = [];
        foreach ($array as $key => $val) {
            $result[$prefix . $key] = $val;
        }

        return $result;
    }

    /**
     * @param string $prefix
     * @param array  $array
     *
     * @return array
     */
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
