<?php namespace FHTeam\LaravelRedisCache\Tests\DataLayer\Coder;

use FHTeam\LaravelRedisCache\DataLayer\Coder\CoderInterface;
use FHTeam\LaravelRedisCache\DataLayer\Coder\PhpSerializeCoder;
use FHTeam\LaravelRedisCache\Tests\TestBase;
use stdClass;

class PhpSerializeCoderTest extends TestBase
{
    /**
     * @var CoderInterface
     */
    protected $coder;

    public function setUp()
    {
        $this->coder = new PhpSerializeCoder();
        parent::setUp();
    }

    public function testEncode()
    {
        $value = ['test1' => 111, 'test2' => 'ajshajhs', 'test3' => new stdClass()];
        $serializedValue = serialize($value);

        $this->assertEquals(['data' => $serializedValue], $this->coder->encode($value));
    }

    public function testDecode()
    {
        $value = ['test1' => 111, 'test2' => 'ajshajhs', 'test3' => new stdClass()];
        $serializedValue = ['data' => serialize($value)];

        $this->assertEquals($value, $this->coder->decode($serializedValue));
    }
}
