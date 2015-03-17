<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\Eloquent;

use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderInterface;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderManagerTrait;

class PivotCoder extends ModelCoder implements CoderInterface
{
    use CoderManagerTrait;

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function decode($value)
    {
        // TODO: Implement decode() method.
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function encode($value)
    {
        // TODO: Implement encode() method.
    }

    protected function encodeCustomFields(array $result)
    {

    }
}
