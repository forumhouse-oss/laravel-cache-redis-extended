<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder;

/**
 * Coder that simply calls PHP's serialize() and unserialize() to encode and decode items
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder
 */
class PhpSerializeCoder implements CoderInterface
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function decode($value)
    {
        return unserialize($value);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function encode($value)
    {
        return serialize($value);
    }
}
