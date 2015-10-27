<?php namespace FHTeam\LaravelRedisCache\TagVersion;

use FHTeam\LaravelRedisCache\TagVersion\Storage\TagVersionStorageInterface;

/**
 * Class TagVersionStorage
 *
 * @package FHTeam\LaravelRedisCache\Tag
 */
class TagVersionManager implements TagVersionManagerInterface
{
    protected $storage;

    /**
     * @param TagVersionStorageInterface $storage
     */
    public function __construct(TagVersionStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function getActualVersionsFor(array $tagNames)
    {
        if (empty($tagNames)) {
            return [];
        }

        $this->storage->cacheTagVersions($tagNames);

        $result = [];

        foreach ($tagNames as $tagName) {
            $result[$tagName] = $this->storage->getTagVersion($tagName);
        }

        return $result;
    }

    public function isAnyTagExpired(array $tags)
    {
        $this->storage->cacheTagVersions(array_keys($tags));

        foreach ($tags as $tagName => $tagVersion) {
            if ($tagVersion < $this->storage->getTagVersion($tagName)) {
                return true;
            }
        }

        return false;
    }

    public function flushTags(array $tagNames)
    {
        $this->storage->flushTags($tagNames);
    }
}
