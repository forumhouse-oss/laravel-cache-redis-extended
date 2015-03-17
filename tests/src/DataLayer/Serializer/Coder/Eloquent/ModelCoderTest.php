<?php

namespace FHTeam\LaravelRedisCache\Tests\DataLayer\Serializer\Coder\Eloquent;

use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderInterface;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\Eloquent\ModelCoder;
use FHTeam\LaravelRedisCache\Tests\DatabaseTestBase;
use FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Models\Bear;

class ModelCoderTest extends DatabaseTestBase
{
    /**
     * @var CoderInterface
     */
    protected $coder;

    public function setUp()
    {
        $this->coder = new ModelCoder();
        parent::setUp();
    }

    public function testEncodeModelPlain()
    {
        $bear = Bear::where('name', 'Lawly')->firstOrFail();
        $this->assertInstanceOf(Bear::class, $bear);

        $expectedAttributes = [
            'type' =>
                "model",
            'class' =>
                Bear::class,
            'attributes' =>
                [
                    'id' => "1",
                    'name' => "Lawly",
                    'type' => "Grizzly",
                    'danger_level' => "8",
                ],
            'relations' => [],
        ];

        $this->assertEquals($expectedAttributes, $this->coder->encode($bear));
    }

    public function testDecodeModelPlain()
    {
        $bear = Bear::where('name', 'Lawly')->firstOrFail();
        $this->assertInstanceOf(Bear::class, $bear);
        $encodedBear = $this->coder->encode($bear);
        $decodedBear = $this->coder->decode($encodedBear);
        $this->assertEquals($bear, $decodedBear);
    }

    public function testEncodeDecodeModelWithRelations()
    {
        $bear = Bear::with('fish', 'trees', 'picnics')->first();

        $this->assertInstanceOf(Bear::class, $bear);
        $encodedBear = $this->coder->encode($bear);
        $decodedBear = $this->coder->decode($encodedBear);
        $this->assertEquals($bear, $decodedBear);
    }
}
