<?php namespace FHTeam\LaravelRedisCache\Utility;

use Illuminate\Redis\Database;

/**
 * Trait for storing and using Redis connections
 *
 * @package FHTeam\LaravelRedisCache\Core
 */
trait RedisConnectionTrait
{
    /**
     * @var string Connection name
     */
    protected $connection;

    /**
     * @var Database Redis database
     */
    protected $redis;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * @param Database $redis
     * @param string   $connection
     * @param string   $prefix
     */
    public function setRedisConnectionData(Database $redis, $connection, $prefix)
    {
        $this->redis = $redis;
        $this->connection = $connection;
        $this->prefix = rtrim($prefix, ':').':';
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
}
