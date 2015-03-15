<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder;

/**
 * Interface CoderInterface
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Serialization\Coder
 */
interface CoderInterface
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function decode($value);

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function encode($value);
}
