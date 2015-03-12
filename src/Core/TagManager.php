<?php

namespace FHTeam\LaravelRedisCache\Core;

use Illuminate\Redis\Database;

/**
 * Class TagManager
 *
 * @package FHTeam\LaravelRedisCache\Tag
 */
class TagManager
{
    private $redis;

    /**
     * IoC invoked constructor
     *
     * @param Database $redis
     */
    public function __construct(Database $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param string[] $tagNames
     *
     * @return bool
     */
    public function anyTagExpired(array $tagNames)
    {

    }

    /**
     * @param string[] $tagNames
     *
     * @return array
     */
    public function getActualTagVersions(array $tagNames)
    {

    }

    /**
     * @param string[] $tagNames
     */
    public function flushTags(array $tagNames)
    {
    }
}
