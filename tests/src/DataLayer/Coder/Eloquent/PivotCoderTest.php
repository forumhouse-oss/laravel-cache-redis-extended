<?php namespace FHTeam\LaravelRedisCache\Tests\DataLayer\Coder\Eloquent;

use Exception;
use FHTeam\LaravelRedisCache\DataLayer\Coder\CoderInterface;
use FHTeam\LaravelRedisCache\DataLayer\Coder\Eloquent\ModelCoder;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\GenericCoderManager;
use FHTeam\LaravelRedisCache\Tests\DatabaseTestBase;
use FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Models\Bear;

class PivotCoderTest extends DatabaseTestBase
{
    /**
     * @var CoderInterface
     */
    protected $coder;

    /**
     * @throws Exception
     */
    public function setUp()
    {
        parent::setUp();
        $this->coder = new ModelCoder();
        $this->coder->setCoderManager(new GenericCoderManager());
    }

    public function testEncodeDecodeModelWithRelations()
    {
        $bear = Bear::with('picnics')->first();

        $this->assertInstanceOf(Bear::class, $bear);
        $encodedBear = $this->coder->encode($bear);
        $decodedBear = $this->coder->decode($encodedBear);
        //$this->markTestIncomplete(
        //    'Need to complete PivotCoder for this to pass'
        //);
        $this->assertEquals($bear, $decodedBear);
    }
}
