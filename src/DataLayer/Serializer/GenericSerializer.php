<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer;

use App;
use FHTeam\LaravelRedisCache\DataLayer\CacheItem;
use FHTeam\LaravelRedisCache\TagVersion\TagVersionManagerInterface;
use FHTeam\LaravelRedisCache\Utility\ArrayTools;
use FHTeam\LaravelRedisCache\Utility\TimeTools;

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
     * @param TagVersionManagerInterface $tagVersionManager
     */
    public function __construct(TagVersionManagerInterface $tagVersionManager)
    {
        $this->tagVersions = $tagVersionManager;
    }

    public function serialize($prefix, array $data, $minutes, $tags)
    {
        $seconds = TimeTools::getTtlInSeconds($minutes);
        $tags = $this->tagVersions->getActualVersionsFor($tags);
        $data = ArrayTools::addPrefixToArrayKeys($prefix, $data);
        /** @var CoderManagerInterface $coder */
        $coder = App::make(CoderManagerInterface::class);

        $data = array_map(
            function ($value) use ($seconds, $tags, $coder) {
                return (string)CacheItem::encode(
                    is_string($value) ? $value : $coder->encode($value),
                    $seconds,
                    $tags
                );
            },
            $data
        );


        return $data;
    }

    public function deserialize($prefix, array $data)
    {
        $data = ArrayTools::stripPrefixFromArrayKeys($prefix, $data);

        $data = array_map(
            function ($cacheItem) {
                return CacheItem::decode($cacheItem);
            },
            $data
        );

        /** @var CoderManagerInterface $coder */
        $coder = App::make(CoderManagerInterface::class);

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

            $value = $item->getValue();
            $item = is_string($value) ? $value : $coder->decode($value);
        }

        return $data;
    }
}
