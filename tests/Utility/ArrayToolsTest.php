<?php

namespace FHTeam\LaravelRedisCache\Tests\Utility;

use FHTeam\LaravelRedisCache\Tests\TestBase;
use FHTeam\LaravelRedisCache\Utility\ArrayTools;

class ArrayToolsTest extends TestBase
{
    public function testAddPrefixToArrayValues()
    {
        $values = ['aaa', 'bbb'];
        $prefixedValues = ArrayTools::addPrefixToArrayValues('prefix:', $values);
        $this->assertEquals(['prefix:aaa', 'prefix:bbb'], $prefixedValues);
    }

    public function testAddPrefixToArrayKeys()
    {
        $values = ['aaa' => '111', 'bbb' => '222'];
        $prefixedValues = ArrayTools::addPrefixToArrayKeys('prefix:', $values);
        $this->assertEquals(['prefix:aaa' => '111', 'prefix:bbb' => '222'], $prefixedValues);
    }

    public function testStripPrefixFromArrayKeys()
    {
        $values = ['prefix:aaa' => '111', 'prefix:bbb' => '222'];
        $prefixedValues = ArrayTools::stripPrefixFromArrayKeys('prefix:', $values);
        $this->assertEquals(['aaa' => '111', 'bbb' => '222'], $prefixedValues);
    }
}
