<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serialization;

use FHTeam\LaravelRedisCache\DataLayer\CacheItem;
use FHTeam\LaravelRedisCache\TagVersionStorage\TagVersionStorageInterface;
use FHTeam\LaravelRedisCache\Utility\Arr;
use FHTeam\LaravelRedisCache\Utility\Time;

/**
 * Class Wrapper
 *
 * @package FHTeam\LaravelRedisCache\DataLayer
 */
class Serializer
{
    /**
     * @var TagVersionStorageInterface
     */
    private $tagVersionStorage;
    /**
     * @var CoderManager
     */
    private $coder;

    /**
     * @param TagVersionStorageInterface $storage
     * @param CoderManager               $coderManager
     */
    public function __construct(TagVersionStorageInterface $storage, CoderManager $coderManager)
    {
        $this->tagVersionStorage = $storage;
        $this->coder = $coderManager;
    }

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
    public function serialize($prefix, array $data, $minutes, $tags)
    {
        $seconds = Time::getTtlInSeconds($minutes);
        $tags = $this->tagVersionStorage->getActualVersionsFor($tags);
        $data = Arr::addPrefixToArrayKeys($prefix, $data);

        $data = array_map(function ($value) use ($seconds, $tags) {
            return CacheItem::encode($this->coder->encode($value), $seconds, $tags);
        }, $data);


        return $data;
    }

    /**
     * @param string $prefix
     * @param array  $data
     *
     * @return array
     * @throws \Exception
     */
    public function deserialize($prefix, array $data)
    {
        $data = Arr::stripPrefixFromArrayKeys($prefix, $data);

        $data = array_map(function ($value) {
            return CacheItem::decode($value);
        }, $data);

        /** @var CacheItem[] $data */
        foreach ($data as &$item) {
            if ($item->isExpired()) {
                $item = null;
                continue;
            }

            if ($this->tagVersionStorage->isAnyTagExpired($item->getTags())) {
                $item = null;
                continue;
            }

            $item = $this->coder->decode($item->getValue());
        }

        return $data;
    }
}