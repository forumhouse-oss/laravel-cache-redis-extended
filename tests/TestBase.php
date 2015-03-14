<?php

namespace FHTeam\LaravelRedisCache\Tests;

use FHTeam\LaravelRedisCache\DataLayer\Serialization\Coder\EloquentCoder;
use FHTeam\LaravelRedisCache\DataLayer\Serialization\CoderManagerInterface;
use FHTeam\LaravelRedisCache\DataLayer\Serialization\GenericCoderManager;
use FHTeam\LaravelRedisCache\DataLayer\Serialization\GenericSerializer;
use FHTeam\LaravelRedisCache\DataLayer\Serialization\SerializerInterface;
use FHTeam\LaravelRedisCache\ServiceProvider\Laravel4ServiceProvider;
use FHTeam\LaravelRedisCache\ServiceProvider\Laravel5ServiceProvider;
use FHTeam\LaravelRedisCache\TagVersion\Storage\PlainRedisTagVersionStorage;
use FHTeam\LaravelRedisCache\TagVersion\Storage\TagVersionStorageInterface;
use FHTeam\LaravelRedisCache\TagVersion\TagVersionManager;
use FHTeam\LaravelRedisCache\TagVersion\TagVersionManagerInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase;

/**
 * Class LaravelAmqpTestBase
 *
 * @package Forumhouse\LaravelAmqp\Tests
 */
class TestBase extends TestCase
{
    /**
     * Get base path.
     *
     * @return string
     */
    protected function getBasePath()
    {
        return __DIR__ . '/../';
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
        /** @var \Illuminate\Config\Repository $config */
        $config = $app['config'];

        // reset base path to point to our package's src directory
        $config->set('cache.driver', 'fh-redis');

        $app->bind(TagVersionManagerInterface::class, TagVersionManager::class);
        $app->bind(TagVersionStorageInterface::class, function () use ($app) {
            return new PlainRedisTagVersionStorage($app['redis'], 'test_connection', 'tag_test');
        });

        $app->bind(SerializerInterface::class, GenericSerializer::class);
        $app->bind(CoderManagerInterface::class, GenericCoderManager::class);

        $config->set('database.redis', [
            'cluster' => false,
            'test_connection' => [
                'host' => 'localhost',
                'port' => 6379,
                'database' => 0,
                'coders' => [
                    Model::class => EloquentCoder::class,
                    Collection::class => EloquentCoder::class
                ],
            ]
        ]);
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
}
