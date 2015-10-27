<?php namespace FHTeam\LaravelRedisCache\DataLayer\Coder;

use FHTeam\LaravelRedisCache\DataLayer\Serializer\CoderManagerInterface;

/**
 * Interface CoderInterface
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Serialization\Coder
 */
interface CoderInterface
{
    /**
     * @param CoderManagerInterface $value
     *
     * @return void
     */
    public function setCoderManager(CoderManagerInterface $value);

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function decode($value);

    /**
     * @param mixed $value
     *
     * @return array
     */
    public function encode($value);
}
