<?php namespace FHTeam\LaravelRedisCache\Tests\DataLayer\Coder\Eloquent;

use FHTeam\LaravelRedisCache\DataLayer\Coder\CoderInterface;
use FHTeam\LaravelRedisCache\DataLayer\Coder\Eloquent\CollectionCoder;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\GenericCoderManager;
use FHTeam\LaravelRedisCache\Tests\DatabaseTestBase;
use FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Models\Bear;
use Illuminate\Database\Eloquent\Collection;

class CollectionCoderTest extends DatabaseTestBase
{
    /**
     * @var CoderInterface
     */
    protected $coder;

    public function setUp()
    {
        parent::setUp();
        $this->coder = new CollectionCoder();
        $this->coder->setCoderManager(new GenericCoderManager());
    }

    public function testEncodeCollectionPlain()
    {
        $bears = Bear::where('name', 'Lawly')->get();
        $this->assertInstanceOf(Collection::class, $bears);
        $this->assertCount(1, $bears);
        $this->assertInstanceOf(Bear::class, $bears[0]);

        $expectedAttributes = [
            'items' => [
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
            ],
        ];
    }

    //public function testEncodeDecodeCollection()
    //{
    //    $bears = Bear::with('fish', 'trees', 'picnics')->get();
    //    $this->assertInstanceOf(Collection::class, $bears);
    //    $encodedBears = $this->coder->encode($bears);
    //    $decodedBears = $this->coder->decode($encodedBears);
    //    $this->assertEquals($bears, $decodedBears);
    //}
}
