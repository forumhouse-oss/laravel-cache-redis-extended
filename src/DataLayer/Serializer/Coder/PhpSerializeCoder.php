<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder;

use Exception;

/**
 * Coder that simply calls PHP's serialize() and unserialize() to encode and decode items
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder
 */
class PhpSerializeCoder implements CoderInterface
{
    use CoderManagerTrait;

    /**
     * @param mixed $value
     *
     * @return mixed
     * @throws Exception
     */
    public function decode($value)
    {
        if (!isset($value['data'])) {
            throw new Exception("No 'data' field at serialized value ".serialize($value));
        }

        return unserialize($value['data']);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function encode($value)
    {
        return ['data' => serialize($value)];
    }
}
