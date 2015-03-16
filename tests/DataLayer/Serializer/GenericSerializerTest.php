<?php

namespace FHTeam\LaravelRedisCache\Tests\DataLayer\Serializer;

use App;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\GenericCoderManager;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\GenericSerializer;
use FHTeam\LaravelRedisCache\TagVersion\Storage\PlainRedisTagVersionStorage;
use FHTeam\LaravelRedisCache\TagVersion\Storage\TagVersionStorageInterface;
use FHTeam\LaravelRedisCache\TagVersion\TagVersionManager;
use FHTeam\LaravelRedisCache\TagVersion\TagVersionManagerInterface;
use FHTeam\LaravelRedisCache\Tests\TestBase;
use stdClass;

class GenericSerializerTest extends TestBase
{
    /**
     * @var TagVersionStorageInterface
     */
    protected $storage;

    /**
     * @var TagVersionManagerInterface
     */
    protected $manager;

    protected $coderManager;

    public function setUp()
    {
        parent::setUp();
        $this->storage = new PlainRedisTagVersionStorage(App::make('redis'), 'test_connection', 'prefix');
        $this->manager = new TagVersionManager($this->storage);
        $this->coderManager = new GenericCoderManager();
    }

    public function testSerializeDeserializeStringSimple()
    {
        $prefix = 'prefix:';


        $data = [
            'key1' => 'StringThing',
            'key2' => 'string_thing2',
            'key3' => 'string_thing_3',
        ];

        $minutes = 121212;

        $tags = ['Tag1', 'Tag2'];

        $serializer = new GenericSerializer($this->manager, $this->coderManager);
        $serialized = $serializer->serialize($prefix, $data, $minutes, $tags);

        $this->assertEquals($data, $serializer->deserialize($prefix, $serialized));
    }

    public function testSerializeDeserializeWithCoders()
    {
        $prefix = 'prefix';

        $obj = new stdClass();
        $obj->test1 = 1111;

        $data = [
            'key1' => 'StringThing',
            'key2' => ['arrayThing', 111, true],
            'key3' => $obj,
        ];

        $minutes = 121212;

        $tags = ['Tag1', 'Tag2'];

        $serializer = new GenericSerializer($this->manager, $this->coderManager);
        $serialized = $serializer->serialize($prefix, $data, $minutes, $tags);

        $this->assertEquals($data, $serializer->deserialize($prefix, $serialized));
    }
}
