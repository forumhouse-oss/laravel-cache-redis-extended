<?php

namespace FHTeam\LaravelRedisCache\Tests\DataLayer;

use FHTeam\LaravelRedisCache\DataLayer\CacheItem;
use FHTeam\LaravelRedisCache\Tests\TestBase;
use Illuminate\Support\Str;

class CacheItemTest extends TestBase
{
    public function testEncodeDecode()
    {
        $length = rand(0, 100);
        $value = [];

        for ($i = 0; $i < $length; $i++) {
            if (rand(0, 1)) {
                $value[] = Str::random();
            } else {
                $value[count($value) - 1] = Str::random();
            }
        }

        $ttl = rand(0, 100000000);
        $tags = ['Tag1', 'Tag2'];

        $encoded = (string)CacheItem::encode($value, $ttl, $tags);

        $decoded = CacheItem::decode($encoded);

        $this->assertEquals($value, $decoded->getValue());
        $this->assertEquals($ttl + time(), $decoded->getExpires());
        $this->assertEquals($tags, $decoded->getTags());
    }
}
