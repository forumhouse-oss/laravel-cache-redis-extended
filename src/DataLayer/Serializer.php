<?php

namespace FHTeam\LaravelRedisCache\DataLayer;

use FHTeam\LaravelRedisCache\DataLayer\Serialization\CoderManager;
use FHTeam\LaravelRedisCache\Utility\TagVersionStorage;
use FHTeam\LaravelRedisCache\Utility\Tools;

/**
 * Class Wrapper
 *
 * @package FHTeam\LaravelRedisCache\DataLayer
 */
class Serializer
{
    /**
     * @var TagVersionStorage
     */
    private $tagVersionStorage;
    /**
     * @var CoderManager
     */
    private $coder;

    /**
     * @param TagVersionStorage $storage
     * @param CoderManager      $coderManager
     */
    public function __construct(TagVersionStorage $storage, CoderManager $coderManager)
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
        $seconds = Tools::getTtlInSeconds($minutes);
        $tags = $this->tagVersionStorage->getActualVersionsFor($tags);
        $data = Tools::addPrefixToArrayKeys($prefix, $data);

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
        $data = Tools::stripPrefixFromArrayKeys($prefix, $data);

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