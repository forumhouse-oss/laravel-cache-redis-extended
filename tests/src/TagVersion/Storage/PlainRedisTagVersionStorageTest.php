<?php

namespace FHTeam\LaravelRedisCache\Tests\TagVersion\Storage;

use App;
use Exception;
use FHTeam\LaravelRedisCache\TagVersion\Storage\PlainRedisTagVersionStorage;
use FHTeam\LaravelRedisCache\TagVersion\Storage\TagVersionStorageInterface;
use FHTeam\LaravelRedisCache\Tests\TestBase;

class PlainRedisTagVersionStorageTest extends TestBase
{
    /**
     * @var TagVersionStorageInterface
     */
    protected $storage;

    public function setUp()
    {
        parent::setUp();
        $this->storage = new PlainRedisTagVersionStorage(App::make('redis'), 'test_connection', 'prefix');
    }

    public function testCacheTagVersions()
    {
        $this->storage->cacheTagVersions(['testCacheTagVersions_1']);
        $this->storage->cacheTagVersions(['testCacheTagVersions_2']);

        $this->assertNotEmpty($this->storage->getTagVersion('testCacheTagVersions_1'));
        $this->assertNotEmpty($this->storage->getTagVersion('testCacheTagVersions_2'));
    }

    public function testGetTagVersion()
    {
        $this->storage->cacheTagVersions(['testGetTagVersion_1']);
        $this->assertNotEmpty($this->storage->getTagVersion('testGetTagVersion_1'));

        $this->setExpectedException(Exception::class);
        $this->storage->getTagVersion('testGetTagVersion_not_exists');
    }

    public function testFlushTags()
    {
        $this->storage->flushTags(['testFlushTags_1'], 11);
        $this->assertEquals(11, $this->storage->getTagVersion('testFlushTags_1'));

        $this->storage->flushTags(['testFlushTags_2'], 22);
        $this->assertEquals(11, $this->storage->getTagVersion('testFlushTags_1'));
        $this->assertEquals(22, $this->storage->getTagVersion('testFlushTags_2'));
    }
}
