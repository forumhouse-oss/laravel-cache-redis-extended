<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serialization;

use FHTeam\LaravelRedisCache\DataLayer\Serialization\Coder\CoderInterface;

/**
 * Entrance class into serialization
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Coder
 */
class CoderManager
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {

        $this->config = $config;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function decode(array $value)
    {
        $coderClass = $value['decoder'];
        /** @var CoderInterface $coder */
        $coder = new $coderClass();
        return $coder->decode($value['data']);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function encode($value)
    {
        $coderClass = $this->getCoderClass($value);
        /** @var CoderInterface $coder */
        $coder = new $coderClass();
        return $coder->encode($value);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function getCoderClass($value)
    {
        //TODO: implement
    }
}
