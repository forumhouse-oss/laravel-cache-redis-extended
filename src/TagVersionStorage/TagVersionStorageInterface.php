<?php

namespace FHTeam\LaravelRedisCache\TagVersionStorage;

/**
 * Interface for tag storage handlers
 *
 * @package FHTeam\LaravelRedisCache\TagVersionStorage
 */
interface TagVersionStorageInterface
{
    /**
     * @param string[] $tagNames
     *
     * @return array
     */
    public function getActualVersionsFor(array $tagNames);

    /**
     * @param array $tagNames
     *
     * @return bool
     */
    public function isAnyTagExpired(array $tagNames);

    /**
     * @param string[] $tagNames
     */
    public function flushTags(array $tagNames);
}
