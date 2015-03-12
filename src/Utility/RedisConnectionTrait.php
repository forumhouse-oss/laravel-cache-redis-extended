<?php


namespace FHTeam\LaravelRedisCache\Utility;

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
     * @param Database $redis
     * @param string   $connection
     */
    public function setRedisConnectionData(Database $redis, $connection)
    {
        $this->redis = $redis;
        $this->connection = $connection;
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
}
