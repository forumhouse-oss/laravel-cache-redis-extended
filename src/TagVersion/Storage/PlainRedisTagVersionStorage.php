<?php

namespace FHTeam\LaravelRedisCache\TagVersion\Storage;

use Exception;
use FHTeam\LaravelRedisCache\Utility\ArrayTools;
use FHTeam\LaravelRedisCache\Utility\RedisConnectionTrait;
use Illuminate\Redis\Database;

/**
 * Class PlainRedisTagVersionStorage
 *
 * @package FHTeam\LaravelRedisCache\TagVersion
 */
class PlainRedisTagVersionStorage implements TagVersionStorageInterface
{
    use RedisConnectionTrait;

    /**
     * Caches actual tag versions to prevent excessive Redis queries
     *
     * @var array<string, int>
     */
    protected $actualTagVersions = [];

    /**
     * @param Database $redis
     * @param string   $connection
     * @param string   $prefix
     */
    public function __construct(Database $redis, $connection, $prefix)
    {
        $this->setRedisConnectionData($redis, $connection, $prefix . ($prefix ? ':' : '') . 'tags');
    }

    public function cacheTagVersions(array $tagNames)
    {
        $needToRequest = array_diff($tagNames, array_keys($this->actualTagVersions));
        $newTagVersions = $this->connection()->mget($needToRequest);
        $newTagVersions = ArrayTools::stripPrefixFromArrayKeys($this->prefix, $newTagVersions);

        //If some tags are new to the server we have to set their current versions
        if (count($newTagVersions) !== count($needToRequest)) {
            $this->flushTags(array_diff($needToRequest, $newTagVersions));
        }

        $this->actualTagVersions = array_merge($this->actualTagVersions, $newTagVersions);
    }

    public function flushTags(array $tagNames)
    {
        $tags = array_flip($tagNames);
        $prefixedTags = ArrayTools::addPrefixToArrayKeys($this->prefix, $tags);
        $version = time();
        foreach ($prefixedTags as &$tagVersion) {
            $tagVersion = $version;
        }
        $this->connection()->mset($prefixedTags);
        $this->actualTagVersions = array_merge($this->actualTagVersions, $tagNames);
    }

    public function getTagVersion($tagName)
    {
        if (!isset($this->actualTagVersions[$tagName])) {
            throw new Exception(
                "Version for tag '$tagName' should be requested using ensureTagVersionsPresent() first"
            );
        }
        return $this->actualTagVersions[$tagName];
    }
}
