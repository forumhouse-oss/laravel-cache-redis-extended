<?php namespace FHTeam\LaravelRedisCache\ServiceProvider;

use FHTeam\LaravelRedisCache\Core\RedisStore;
use Illuminate\Contracts\Cache\Store;

/**
 * Class Laravel4RedisStore
 *
 * @package Forumhouse\LaravelAmqp\ServiceProvider
 */
class Laravel5RedisStore extends RedisStore implements Store
{

}
