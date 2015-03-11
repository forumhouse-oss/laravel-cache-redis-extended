<?php

namespace FHTeam\LaravelRedisCache\Core;

use Carbon\Carbon;
use Closure;
use DateTime;
use FHTeam\LaravelRedisCache\Coder\CoderManager;
use FHTeam\LaravelRedisCache\Tag\TagReader;
use Illuminate\Cache\TaggableStore;
use Illuminate\Redis\Database as Redis;

class RedisStore extends TaggableStore
{
    /*
     * Fields of the serialized object in cache
     */

    /** When this object expire: int */
    const IDX_EXPIRES = 'expires';

    /** Object tags: string name => int version */
    const IDX_TAGS = 'tags';

    /** Object value: mixed */
    const IDX_RAW_VALUE = 'value';

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
     * @var array
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
        $this->prefix = strlen($prefix) > 0 ? $prefix . ':' : '';
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

    public function has($key)
    {
        return null !== $this->get($key);
    }

    public function add($key)
    {
        //TODO: Redis logic
    }

    public function remember($key, $minutes, Closure $callback)
    {

    }

    public function rememberForever($key, Closure $callback)
    {

    }

    public function sear($key, Closure $callback)
    {

    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param array|string $key
     * @param  mixed       $value
     * @param  int         $minutes
     *
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        $minutes = max(1, $minutes);

        if (is_array($key)) {
            $this->mset($key, $minutes);
        } else {
            $this->mset([$key => $value], $minutes);
        }
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
        $value = is_numeric($value) ? $value : serialize($value);

        $this->connection()->set($this->prefix . $key, $value);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string $key
     *
     * @return void
     */
    public function forget($key)
    {
        $this->connection()->del($this->prefix . $key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        //TODO: with tags flushes only them
        $this->connection()->flushdb();
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
        return (new static($this->redis, $this->prefix,
            $this->connection))->setTags(is_array($names) ? $names : func_get_args());
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
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
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

        $keyValues = $this->connection()->mget($keys);

        return array_map([$this, 'unwrapValue'], $keyValues);
    }

    /**
     * @param array $values
     * @param int   $minutes
     *
     * @return void
     */
    public function mset(array $values, $minutes)
    {
        $result = [];
        $minutes = $this->getMinutes($minutes) * 60;
        foreach ($values as $key => $val) {
            $result[$this->prefix . $key] = $this->wrapValue($val, $minutes);
        }

        $transaction = $keyValues = $this->connection()->transaction();
        foreach ($result as $key => $value) {
            $transaction->setex($key, $minutes, $value);
        }
        $transaction->execute();
    }

    /**
     * Calculate the number of minutes with the given duration.
     *
     * @param  \DateTime|int $duration
     *
     * @return int|null
     */
    protected function getMinutes($duration)
    {
        if ($duration instanceof DateTime) {
            $fromNow = Carbon::instance($duration)->diffInMinutes();

            return $fromNow > 0 ? $fromNow : null;
        }

        return is_string($duration) ? (int)$duration : $duration;
    }

    /**
     * @param string $rawValue
     *
     * @return mixed
     */
    protected function unwrapValue($rawValue)
    {
        if (null === $rawValue) {
            return null;
        }

        $rawValue = unserialize($rawValue);

        //is cache itself expired?
        if ($rawValue[self::IDX_EXPIRES] < time()) {
            return null;
        }

        $tagReader = new TagReader($this->redis, $rawValue[self::IDX_TAGS]);

        if ($tagReader->anyTagExpired()) {
            return null;
        }

        $value = $this->getCoderManager()->decode($rawValue[self::IDX_RAW_VALUE]);

        return $value;
    }

    /**
     * @param $value
     * @param $expires
     *
     * @return mixed
     */
    protected function wrapValue($value, $expires)
    {
        $tagReader = new TagReader($this->redis, $this->tags);

        $data = [
            self::IDX_EXPIRES => $expires,
            self::IDX_TAGS => $tagReader->getActualTagVersions(),
            self::IDX_RAW_VALUE => $this->getCoderManager()->encode($value),
        ];

        return serialize($data);
    }

    /**
     * @return CoderManager
     */
    private function getCoderManager()
    {

    }

    /**
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }
}
