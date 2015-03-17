<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder;

use FHTeam\LaravelRedisCache\DataLayer\Serializer\CoderManagerInterface;

trait CoderManagerTrait
{
    /**
     * @var CoderManagerInterface
     */
    protected $coderManager;

    /**
     * @param CoderManagerInterface $value
     *
     * @return void
     */
    public function setCoderManager(CoderManagerInterface $value)
    {
        $this->coderManager = $value;
    }

    /**
     * @param mixed $value
     *
     * @return array
     */
    public function encodeAny($value)
    {
        return $this->coderManager->encode($value);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function decodeAny($value)
    {
        return $this->coderManager->decode($value);
    }
}
