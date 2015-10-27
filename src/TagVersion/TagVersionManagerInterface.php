<?php namespace FHTeam\LaravelRedisCache\TagVersion;

/**
 * Interface for tag version manager
 *
 * @package FHTeam\LaravelRedisCache\TagVersionStorage
 */
interface TagVersionManagerInterface
{
    /**
     * Given array of string tag names, returns array<string, int> of tag versions
     *
     * @param string[] $tagNames
     *
     * @return array<string, int>
     */
    public function getActualVersionsFor(array $tagNames);

    /**
     * Checks if any of the tags expired in a given array
     *
     * @param array <string, int> $tags
     *
     * @return bool
     */
    public function isAnyTagExpired(array $tags);

    /**
     * Flushes given tags by setting their version to the current one
     *
     * @param string[] $tagNames
     */
    public function flushTags(array $tagNames);
}
