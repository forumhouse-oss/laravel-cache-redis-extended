<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serialization;

use FHTeam\LaravelRedisCache\DataLayer\CacheItem;
use FHTeam\LaravelRedisCache\TagVersion\TagVersionManagerInterface;
use FHTeam\LaravelRedisCache\Utility\ArrayTools;
use FHTeam\LaravelRedisCache\Utility\Time;

/**
 * Generic serializer implementation
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Serialization
 */
class GenericSerializer implements SerializerInterface
{
    /**
     * @var TagVersionManagerInterface
     */
    private $tagVersions;
    /**
     * @var CoderManagerInterface
     */
    private $coder;

    /**
     * @param TagVersionManagerInterface $tagVersionManager
     * @param CoderManagerInterface      $coderManager
     */
    public function __construct(TagVersionManagerInterface $tagVersionManager, CoderManagerInterface $coderManager)
    {
        $this->tagVersions = $tagVersionManager;
        $this->coder = $coderManager;
    }

    public function serialize($prefix, array $data, $minutes, $tags)
    {
        $seconds = Time::getTtlInSeconds($minutes);
        $tags = $this->tagVersions->getActualVersionsFor($tags);
        $data = ArrayTools::addPrefixToArrayKeys($prefix, $data);

        $data = array_map(function ($value) use ($seconds, $tags) {
            return CacheItem::encode(is_string($value) ? $value : $this->coder->encode($value), $seconds, $tags);
        }, $data);


        return $data;
    }

    public function deserialize($prefix, array $data)
    {
        $data = ArrayTools::stripPrefixFromArrayKeys($prefix, $data);

        $data = array_map(function ($value) {
            return is_string($value) ? $value : CacheItem::decode($value);
        }, $data);

        /** @var CacheItem[] $data */
        foreach ($data as &$item) {
            if ($item->isExpired()) {
                $item = null;
                continue;
            }

            if ($this->tagVersions->isAnyTagExpired($item->getTags())) {
                $item = null;
                continue;
            }

            $item = $this->coder->decode($item->getValue());
        }

        return $data;
    }
}
