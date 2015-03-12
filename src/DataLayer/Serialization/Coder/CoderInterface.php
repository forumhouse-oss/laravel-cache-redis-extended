<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serialization\Coder;

interface CoderInterface
{
    public function decode($value);

    public function encode($value);
}
