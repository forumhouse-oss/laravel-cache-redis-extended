<?php

namespace FHTeam\LaravelRedisCache\Tests;

use Cache;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\EloquentCoder;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\PhpSerializeCoder;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\CoderManagerInterface;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\GenericCoderManager;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\GenericSerializer;
use FHTeam\LaravelRedisCache\ServiceProvider\Laravel4ServiceProvider;
use FHTeam\LaravelRedisCache\ServiceProvider\Laravel5ServiceProvider;
use FHTeam\LaravelRedisCache\TagVersion\Storage\PlainRedisTagVersionStorage;
use FHTeam\LaravelRedisCache\TagVersion\Storage\TagVersionStorageInterface;
use FHTeam\LaravelRedisCache\TagVersion\TagVersionManager;
use FHTeam\LaravelRedisCache\TagVersion\TagVersionManagerInterface;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase;
use stdClass;
use SuperClosure\SerializerInterface;

/**
 * Class LaravelAmqpTestBase
 *
 * @package Forumhouse\LaravelAmqp\Tests
 */
class TestBase extends TestCase
{
    public function tearDown()
    {
        Cache::flush();
    }

    /**
     * Get base path.
     *
     * @return string
     */
    protected function getBasePath()
    {
        return realpath(__DIR__ . '/../');
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        /** @var Repository $config */
        $config = $app['config'];

        $app->bind(TagVersionManagerInterface::class, TagVersionManager::class);
        $app->bind(TagVersionStorageInterface::class, function () use ($app) {
            return new PlainRedisTagVersionStorage($app['redis'], 'test_connection', 'tag_test');
        });

        $app->bind(SerializerInterface::class, GenericSerializer::class);
        $app->bind(CoderManagerInterface::class, GenericCoderManager::class);

        $config->set('database.redis', [
            'cluster' => false,
            'test_connection' => [
                'host' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
            ]
        ]);

        $this->setCacheConfiguration($config);
    }

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        if (version_compare(Application::VERSION, "5.0", '>=')) {
            return [Laravel5ServiceProvider::class,];
        } else {
            return [Laravel4ServiceProvider::class,];
        }
    }

    /**
     * Getting rid of unused service providers
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getApplicationProviders($app)
    {
        return [
            'Illuminate\Foundation\Providers\ArtisanServiceProvider',
            'Illuminate\Cache\CacheServiceProvider',
            'Illuminate\Foundation\Providers\ConsoleSupportServiceProvider',
            'Illuminate\Database\DatabaseServiceProvider',
            'Orchestra\Database\MigrationServiceProvider',
            'Illuminate\Redis\RedisServiceProvider',
            'Illuminate\Database\SeedServiceProvider',
        ];
    }

    /**
     * @param Repository $config
     */
    protected function setCacheConfiguration(Repository $config)
    {

        if (version_compare(Application::VERSION, "5.0", '>=')) {
            $cacheConfigKey = 'cache.stores.redis';
        } else {
            $cacheConfigKey = 'cache';
        }

        $config->set(
            $cacheConfigKey,
            [
                'driver' => 'fh-redis',
                'connection' => 'test_connection',
                'prefix' => 'prefix',
                'coders' => [
                    Model::class => EloquentCoder::class,
                    Collection::class => EloquentCoder::class,
                    stdClass::class => PhpSerializeCoder::class,
                    'arrays' => function ($value) {
                        if (is_array($value)) {
                            return new PhpSerializeCoder();
                        }
                        return null;
                    },
                ],
            ]
        );
    }
}
