<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serialization;

use FHTeam\LaravelRedisCache\DataLayer\CacheItem;
use FHTeam\LaravelRedisCache\TagVersion\TagVersionManagerInterface;
use FHTeam\LaravelRedisCache\Utility\ArrayTools;
use FHTeam\LaravelRedisCache\Utility\Time;

/**
 * Class Wrapper
 *
 * @package FHTeam\LaravelRedisCache\DataLayer
 */
class Serializer
{
    /**
     * @var TagVersionManagerInterface
     */
    private $tagVersionStorage;
    /**
     * @var CoderManager
     */
    private $coder;

    /**
     * @param TagVersionManagerInterface $storage
     * @param CoderManager               $coderManager
     */
    public function __construct(TagVersionManagerInterface $storage, CoderManager $coderManager)
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
        $data = ArrayTools::addPrefixToArrayKeys($prefix, $data);

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
        $data = ArrayTools::stripPrefixFromArrayKeys($prefix, $data);

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