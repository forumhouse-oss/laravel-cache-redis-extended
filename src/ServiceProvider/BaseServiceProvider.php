<?php

namespace FHTeam\LaravelRedisCache\ServiceProvider;

use FHTeam\LaravelRedisCache\Utility\TagVersionStorage;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Redis\Database;
use Illuminate\Support\ServiceProvider;

/**
 * Class BaseServiceProvider
 *
 * @package Forumhouse\LaravelAmqp\ServiceProvider
 */
abstract class BaseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->booted(function ($app) {
            /** @var Database $redis */
            /** @var CacheManager $cacheManager */
            /** @var \Illuminate\Contracts\Foundation\Application $app */
            $cacheManager = $app['cache'];
            $redis = $app['redis'];

            // Registering cache driver
            $cacheManager->extend('amqp', function ($app, array $config) use ($cacheManager, $redis) {
                $connection = array_get($config, 'connection', 'default') ?: 'default';
                return $this->getRepository($cacheManager, $redis, $this->getPrefix($config), $connection);
            });

            // Registering TagVersionStorage to share actual tag versions
            $this->app->bind(TagVersionStorage::class, function ($connection) use ($redis) {
                static $cache = [];
                if (isset($cache[$connection])) {
                    return $cache[$connection];
                }

                return $cache[$connection] = new TagVersionStorage($redis, $connection);
            });
        });
    }

    /**
     * @param CacheManager $cacheManager
     * @param Database     $redis
     * @param string       $prefix
     * @param string       $connection
     *
     * @return Repository
     */
    abstract protected function getRepository(CacheManager $cacheManager, Database $redis, $prefix, $connection);

    /**
     * Returns cache prefix
     *
     * @param array $config
     *
     * @return string
     */
    private function getPrefix(array $config)
    {
        return array_get($config, 'prefix') ?: $this->app['config']['cache.prefix'];
    }
}
