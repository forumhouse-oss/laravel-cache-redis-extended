<?php

namespace FHTeam\LaravelRedisCache\ServiceProvider;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Redis\Database;

/**
 * Service provider for AMQP queues
 *
 * @package Forumhouse\LaravelAmqp\ServiceProvider
 */
class Laravel4ServiceProvider extends BaseServiceProvider
{
    /**
     * @param CacheManager $cacheManager
     * @param Database     $redis
     * @param string       $prefix
     * @param string       $connection
     *
     * @return Repository
     */
    protected function getRepository(CacheManager $cacheManager, Database $redis, $prefix, $connection)
    {
        return $cacheManager->repository(new Laravel4RedisStore($redis, $prefix, $connection));
    }
}
