<?php namespace FHTeam\LaravelRedisCache\DataLayer\Serializer;

use Carbon\Carbon;

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
     * @param string     $prefix
     * @param array      $data
     * @param int|Carbon $minutes
     * @param string[]   $tags
     *
     * @return array
     */
    public function serialize($prefix, array $data, $minutes, array $tags);

    /**
     * @param string $prefix
     * @param array  $data
     *
     * @return array
     * @throws \Exception
     */
    public function deserialize($prefix, array $data);
}
