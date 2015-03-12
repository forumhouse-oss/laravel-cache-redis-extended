<?php

namespace FHTeam\LaravelRedisCache\Core;

use Carbon\Carbon;
use Closure;
use DateTime;
use Exception;
use FHTeam\LaravelRedisCache\DataLayer\Wrapper;
use Illuminate\Cache\TaggableStore;
use Illuminate\Redis\Database as Redis;

/**
 * Class RedisStore
 *
 * @package FHTeam\LaravelRedisCache\Core
 */
class RedisStore extends TaggableStore
{
    /**
     * The Redis database connection.
     *
     * @var \Illuminate\Redis\Database
     */
    protected $redis;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The Redis connection that should be used.
     *
     * @var string
     */
    protected $connection;

    /**
     * @var Wrapper
     */
    protected $wrapper;

    /**
     * @var TagManager
     */
    protected $tagManager;

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
     */
    public function __construct(Redis $redis, $prefix = '', $connection = 'default')
    {
        $this->redis = $redis;
        $this->connection = $connection;
        $this->prefix = $this->makePrefix($prefix);
        $this->tagManager = new TagManager($redis);
        $this->wrapper = new Wrapper($redis, $this->tagManager, $this->prefix);
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
        return $this->connection()->exists($this->prefix . $key);
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
        return $this->connection()->incrby($this->prefix . $key, $value);
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
        return $this->connection()->decrby($this->prefix . $key, $value);
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
        $key = $this->getPrefixedKeys($key);
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
            $this->tagManager->flushTags($this->tags);
        } else {
            $this->connection()->flushdb();
        }
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
        $self = new static($this->redis, $this->prefix, $this->connection);
        return $self->setTags(is_array($names) ? $names : func_get_args());
    }

    /**
     * Get the Redis connection instance.
     *
     * @return \Predis\Client
     */
    public function connection()
    {
        return $this->redis->connection($this->connection);
    }

    /**
     * Set the connection name to be used.
     *
     * @param  string $connection
     *
     * @return void
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the Redis database instance.
     *
     * @return \Illuminate\Redis\Database
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @param array $keys
     *
     * @return array
     */
    public function mget(array $keys)
    {
        $keys = array_map(function ($value) {
            return $this->prefix . $value;
        }, $keys);

        $data = $this->connection()->mget($keys);

        return $this->wrapper->unwrapData($data);
    }

    /**
     * @param array $values
     * @param int   $minutes
     * @param bool  $nxOnly Only set non-existent keys
     *
     * @throws Exception
     * @throws \Predis\Response\ServerException
     * @throws \Predis\Transaction\AbortedMultiExecException
     */
    public function mset(array $values, $minutes, $nxOnly = false)
    {
        $seconds = $this->getTtlInSeconds($minutes);
        $result = $this->wrapper->wrapData($values, $seconds, $this->tags);

        // Executing MULTI only if we transfer more than one item
        $redis = ((count($values) > 1) ? $this->connection()->transaction() : $this->connection());

        //Building MSET command with arguments we need
        $arguments = [
            'key' => '', // Arg #1
            'value' => '', // Arg #2
        ];

        if (0 !== $minutes) {
            $arguments['ex'] = 'EX'; // Arg #3
            $arguments['seconds'] = $seconds; // Arg #4
        }

        if ($nxOnly) {
            $arguments['nx'] = 'NX'; // Arg #5
        }

        foreach ($result as $key => $value) {
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
     * Calculate the number of minutes with the given duration.
     *
     * @param  \DateTime|int $minutes Either the exact date at which the item expire, or ttl in minutes
     *
     * @return int A number of seconds to use in Redis commands
     * @throws Exception
     */
    protected function getTtlInSeconds($minutes)
    {
        if ($minutes instanceof DateTime) {
            $fromNow = Carbon::instance($minutes)->diffInSeconds();
            if ($fromNow < 0) {
                throw new Exception("Cache TTL should be >=0");
            }
            return $fromNow;
        }

        return $minutes * 60;
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    protected function makePrefix($prefix)
    {
        if (strlen($prefix) === 0) {
            return '';
        }

        return rtrim($prefix, ':') . ':';
    }

    /**
     * @param array $keys
     *
     * @return array
     */
    protected function getPrefixedKeys(array $keys)
    {
        $key = array_map(function ($value) {
            return $this->prefix . $value;
        }, $keys);

        return $key;
    }

    /**
     * @param array $tags
     */
    protected function setTags($tags)
    {
        $this->tags = $tags;
    }
}
