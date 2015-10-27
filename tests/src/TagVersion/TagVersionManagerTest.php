<?php namespace FHTeam\LaravelRedisCache\Tests\TagVersion;

use App;
use FHTeam\LaravelRedisCache\TagVersion\Storage\PlainRedisTagVersionStorage;
use FHTeam\LaravelRedisCache\TagVersion\TagVersionManager;
use FHTeam\LaravelRedisCache\Tests\TestBase;
use Illuminate\Redis\Database;

class TagVersionManagerTest extends TestBase
{
    /**
     * @var PlainRedisTagVersionStorage
     */
    protected $storage;

    /**
     * @var TagVersionManager
     */
    protected $manager;

    public function setUp()
    {
        parent::setUp();

        /** @var Database $redis */
        $redis = App::make('redis');
        $redis->connection('test_connection')->flushall();

        $this->storage = new PlainRedisTagVersionStorage($redis, 'test_connection', 'prefix');
        $this->manager = new TagVersionManager($this->storage);
    }

    public function testGetActualVersionsFor()
    {
        $tags = ['testGetActualVersionsFor_0', 'testGetActualVersionsFor_1'];
        $versions = $this->manager->getActualVersionsFor($tags);

        $tags[] = 'testGetActualVersionsFor_2';
        $moreVersions = $this->manager->getActualVersionsFor($tags);

        $this->assertGreaterThan(1, $moreVersions['testGetActualVersionsFor_0']);
        $this->assertGreaterThan(1, $moreVersions['testGetActualVersionsFor_1']);

        $this->assertEquals($moreVersions['testGetActualVersionsFor_0'], $versions['testGetActualVersionsFor_0']);
        $this->assertEquals($moreVersions['testGetActualVersionsFor_1'], $versions['testGetActualVersionsFor_1']);

        $this->assertNotEmpty($moreVersions['testGetActualVersionsFor_2']);
    }

    public function testAnyTagExpired()
    {
        $tags = ['testAnyTagExpired_0', 'testAnyTagExpired_1'];
        $this->manager->getActualVersionsFor($tags);
        $this->assertTrue($this->manager->isAnyTagExpired(["testAnyTagExpired_0" => time() - 1000]));
        $this->assertFalse($this->manager->isAnyTagExpired(["testAnyTagExpired_3" => time()]));
    }
}
