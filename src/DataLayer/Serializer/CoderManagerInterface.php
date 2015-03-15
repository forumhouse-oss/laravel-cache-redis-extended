<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer;

/**
 * Entrance class into serialization
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Coder
 */
interface CoderManagerInterface
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function decode(array $value);

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function encode($value);
}
