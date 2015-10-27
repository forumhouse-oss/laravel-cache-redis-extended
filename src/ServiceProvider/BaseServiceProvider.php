<?php namespace FHTeam\LaravelRedisCache\ServiceProvider;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Foundation\Application;
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
        $this->app->booted(
            function ($app) {
                /** @var CacheManager $cacheManager */
                $cacheManager = $app['cache'];

                // Registering cache driver
                $cacheManager->extend(
                    'fh-redis',
                    function ($app, array $config) use ($cacheManager) {

                        /** @var Application $app */
                        /** @var Database $redis */
                        $redis = $app['redis'];
                        $connection = array_get($config, 'connection', 'default') ?: 'default';
                        $prefix = $this->getPrefix($config);

                        return $this->getRepository($cacheManager, $redis, $prefix, $connection);
                    }
                );
            }
        );
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
