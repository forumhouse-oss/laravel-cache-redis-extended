<?php

namespace FHTeam\LaravelRedisCache\Utility;

use Illuminate\Redis\Database;

/**
 * Class TagVersionStorage
 *
 * @package FHTeam\LaravelRedisCache\Tag
 */
class TagVersionStorage
{
    use RedisConnectionTrait;

    /**
     * @var array<string, int>
     */
    protected $actualTagVersions;

    /**
     * @param Database $redis
     * @param string   $connection
     */
    public function __construct(Database $redis, $connection)
    {
        $this->setRedisConnectionData($redis, $connection);
    }

    /**
     * @param string[] $tagNames
     *
     * @return array
     */
    public function getActualVersionsFor(array $tagNames)
    {
        if (empty($tagNames)) {
            return [];
        }

        //TODO: fetch actual tag versions from Redis
    }

    /**
     * @param array $tagNames
     *
     * @return bool
     */
    public function isAnyTagExpired(array $tagNames)
    {
        //TODO: fetch missing actual tag versions and check
    }

    /**
     * @param string[] $tagNames
     */
    public function flushTags(array $tagNames)
    {
        //TODO: Flush tags in Redis
    }
}
