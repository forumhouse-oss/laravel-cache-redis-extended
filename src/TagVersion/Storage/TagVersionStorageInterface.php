<?php

namespace FHTeam\LaravelRedisCache\TagVersion\Storage;

use Exception;

interface TagVersionStorageInterface
{
    /**
     * Ensures we have latest versions in the storage for all given tag names
     *
     * @param array $tagNames
     */
    public function cacheTagVersions(array $tagNames);

    /**
     * Updates tag versions to current
     *
     * @param array $tagNames
     */
    public function flushTags(array $tagNames);

    /**
     * Returns current tag version
     *
     * @param string $tagName
     *
     * @return int
     * @throws Exception
     */
    public function getTagVersion($tagName);
}
