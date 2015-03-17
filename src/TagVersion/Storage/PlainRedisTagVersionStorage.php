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
        $this->setRedisConnectionData($redis, $connection, $prefix.($prefix ? ':' : '').'tags');
    }

    public function cacheTagVersions(array $tagNames)
    {
        // array_values here used for reindexing array from 0
        $needToRequest = array_values(array_diff($tagNames, array_keys($this->actualTagVersions)));

        if (count($needToRequest) < 1) {
            return;
        }

        $needToRequestPrefixed = ArrayTools::addPrefixToArrayValues($this->prefix, $needToRequest);
        $newTagVersions = $this->connection()->mget($needToRequestPrefixed);
        $newTagVersions = array_combine($needToRequest, $newTagVersions);


        $flush = [];
        foreach ($newTagVersions as $newTagKey => $newTagVersion) {
            if (!$newTagVersion) {
                $flush[] = $newTagKey;
            }
        }

        $flush = $this->flushTags($flush);
        $this->actualTagVersions = array_merge($this->actualTagVersions, $newTagVersions, $flush);
    }

    public function flushTags(array $tagNames, $version = null)
    {
        if (empty($tagNames)) {
            return [];
        }
        $tags = array_flip($tagNames);
        $version = $version ?: time();
        foreach ($tags as &$tagVersion) {
            $tagVersion = $version;
        }
        $prefixedTags = ArrayTools::addPrefixToArrayKeys($this->prefix, $tags);
        $this->connection()->mset($prefixedTags);
        $this->actualTagVersions = array_merge($this->actualTagVersions, $tags);

        return $tags;
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
