<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer;

use Config;
use Exception;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderInterface;

/**
 * Class to manage coders
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Serialization
 */
class GenericCoderManager implements CoderManagerInterface
{
    public function decode(array $value)
    {
        $coderClass = $value['coder'];

        if (!class_exists($coderClass)) {
            throw new Exception("Cannot load coder class '$coderClass'");
        }

        /** @var CoderInterface $coder */
        $coder = new $coderClass();
        return $coder->decode($value['data']);
    }

    public function encode($value)
    {
        $coder = $this->getCoder($value);
        return ['coder' => get_class($coder), 'data' => $coder->encode($value)];
    }

    /**
     * @param mixed $value
     *
     * @return CoderInterface
     * @throws Exception
     */
    protected function getCoder($value)
    {
        $connection = Config::get('cache.connection', 'default');

        $coderConfiguration = Config::get("database.redis.{$connection}.coders");

        if (!is_array($coderConfiguration)) {
            throw new Exception("You should configure coders at 'database.redis.{$connection}.coders'");
        }

        foreach ($coderConfiguration as $valueClass => $coderData) {
            if (is_callable($coderData) && ($coderClass = $coderData($value))) {
                return $coderClass;
            }

            if (is_string($coderData) && ($value instanceof $valueClass)) {
                return new $coderData();
            }

            throw new Exception("Unable to treat value as a config for a coder: " . serialize($coderData));
        }

        throw new Exception("No coder found to encode value: " . serialize($value));
    }
}
