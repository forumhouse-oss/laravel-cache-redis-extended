<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serialization;

/**
 * Interface to be implemented by all serializers
 *
 * @package FHTeam\LaravelRedisCache\DataLayer
 */
interface SerializerInterface
{
    /**
     * Prepares data to be sent to the cache using any command
     *
     * @param       $prefix
     * @param array $data
     * @param int   $minutes
     * @param       $tags
     *
     * @return array
     * @throws \Exception
     */
    public function serialize($prefix, array $data, $minutes, $tags);

    /**
     * @param string $prefix
     * @param array  $data
     *
     * @return array
     * @throws \Exception
     */
    public function deserialize($prefix, array $data);
}
