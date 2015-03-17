<?php

namespace FHTeam\LaravelRedisCache\Core;

use App;
use Closure;
use DateTime;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\SerializerInterface;
use FHTeam\LaravelRedisCache\TagVersion\TagVersionManagerInterface;
use FHTeam\LaravelRedisCache\Utility\ArrayTools;
use FHTeam\LaravelRedisCache\Utility\RedisConnectionTrait;
use FHTeam\LaravelRedisCache\Utility\TimeTools;
use Illuminate\Cache\TaggableStore;
use Illuminate\Redis\Database as Redis;

/**
 * Class RedisStore
 *
 * @package FHTeam\LaravelRedisCache\Core
 */
class RedisStore extends TaggableStore
{
    use RedisConnectionTrait;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var TagVersionManagerInterface
     */
    protected $tagVersions;

    /**
     * @var string[] Tag names, that are attached to this store instance
     */
    protected $tags;

    /**
     * Create a new Redis store.
     *
     * @param  \Illuminate\Redis\Database $redis
     * @param  string                     $prefix
     * @param  string                     $connection
     * @param array                       $tags
     */
    public function __construct(Redis $redis, $prefix = '', $connection = 'default', $tags = [])
    {
        $this->setRedisConnectionData($redis, $connection, $prefix);
        $this->tags = $tags;
        $this->tagVersions = App::make(TagVersionManagerInterface::class);
        $this->serializer = App::make(SerializerInterface::class);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param array|string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (is_array($key)) {
            return $this->mget($key);
        } else {
            return $this->mget([$key])[0];
        }
    }

    /**
     * Checks if the key exists. This maps to Redis EXISTS command to prevent excessive traffic
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->connection()->exists($this->prefix.$key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $minutes
     */
    public function add($key, $value, $minutes)
    {
        if (!is_array($key)) {
            $key = [$key => $value];
        }

        $this->mset($key, $minutes, true);
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param  string        $key
     * @param  \DateTime|int $minutes
     * @param  \Closure      $callback
     *
     * @return mixed
     */
    public function remember($key, $minutes, Closure $callback)
    {
        // If the item exists in the cache we will just return this immediately
        // otherwise we will execute the given Closure and cache the result
        // of that execution for the given number of minutes in storage.
        if (!is_null($value = $this->get($key))) {
            return $value;
        }

        $this->put($key, $value = $callback(), $minutes);

        return $value;
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param  string   $key
     * @param  \Closure $callback
     *
     * @return mixed
     */
    public function rememberForever($key, Closure $callback)
    {
        // If the item exists in the cache we will just return this immediately
        // otherwise we will execute the given Closure and cache the result
        // of that execution for the given number of minutes. It's easy.
        if (!is_null($value = $this->get($key))) {
            return $value;
        }

        $this->put($key, $value = $callback(), 0);

        return $value;
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param  string   $key
     * @param  \Closure $callback
     *
     * @return mixed
     */
    public function sear($key, Closure $callback)
    {
        return $this->rememberForever($key, $callback);
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param array|string  $key
     * @param  mixed        $value
     * @param  DateTime|int $minutes
     *
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        if (!is_array($key)) {
            $key = [$key => $value];
        }

        $this->mset($key, $minutes);
    }

    /**
     * Increment the value of an item in the cache.
     * WARNING: this operation ignores tagging!
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return $this->connection()->incrby($this->prefix.$key, $value);
    }

    /**
     * Increment the value of an item in the cache.
     * WARNING: this operation ignores tagging!
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->connection()->decrby($this->prefix.$key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }

    /**
     * Remove an item or items from the cache. This batches multiple key deletions into a single Redis DEL command
     *
     * @param array|string $key
     *
     * @return void
     */
    public function forget($key)
    {
        $key = (array)$key;
        $key = ArrayTools::addPrefixToArrayValues($this->prefix, $key);
        $this->connection()->del($key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        if (!empty($this->tags)) {
            $this->tagVersions->flushTags($this->tags);
        } else {
            $this->connection()->flushdb();
        }
    }

    /**
     * @param array $values
     * @param int   $minutes
     * @param bool  $nxOnly Only set non-existent keys
     *
     * @throws \Predis\Response\ServerException
     * @throws \Predis\Transaction\AbortedMultiExecException
     */
    public function mset(array $values, $minutes, $nxOnly = false)
    {
        $values = $this->serializer->serialize($this->prefix, $values, $minutes, $this->tags);

        // Executing MULTI only if we transfer more than one item
        $redis = ((count($values) > 1) ? $this->connection()->transaction() : $this->connection());

        //Building MSET command with arguments we need
        $arguments = [
            'key' => '', // Arg #1
            'value' => '', // Arg #2
        ];

        if (0 !== $minutes) {
            $arguments['ex'] = 'EX'; // Arg #3
            $arguments['seconds'] = TimeTools::getTtlInSeconds($minutes); // Arg #4
        }

        if ($nxOnly) {
            $arguments['nx'] = 'NX'; // Arg #5
        }

        foreach ($values as $key => $value) {
            $arguments['key'] = $key;
            $arguments['value'] = $value;

            call_user_func_array([$redis, 'set'], $arguments);
        }

        // Executing Redis EXEC if needed
        if (count($values) > 1) {
            $redis->execute();
        }
    }

    /**
     * @param array $keys
     *
     * @return array|void
     */
    public function mget(array $keys)
    {
        $keys = ArrayTools::addPrefixToArrayValues($this->prefix, $keys);
        $data = $this->connection()->mget($keys);
        $data = $this->serializer->deserialize($this->prefix, $data);

        return $data;
    }


    /**
     * Begin executing a new tags operation.
     *
     * @param  array|mixed $names
     *
     * @return \Illuminate\Cache\RedisTaggedCache
     */
    public function tags($names)
    {
        $tags = is_array($names) ? $names : func_get_args();

        return new static($this->redis, $this->prefix, $this->connection, $tags);
    }
}
