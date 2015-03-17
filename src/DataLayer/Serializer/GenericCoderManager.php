<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer;

use Config;
use Exception;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderInterface;
use Illuminate\Foundation\Application;

/**
 * Class to manage coders
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Serialization
 */
class GenericCoderManager implements CoderManagerInterface
{
    /**
     * @var array
     */
    protected $coderConfig = [];

    /**
     * @param null|array $config Configuration for coders or null to fetch from Laravel configuration
     *
     * @throws Exception
     */
    public function __construct($config = null)
    {
        if (version_compare(Application::VERSION, "5.0", '>=')) {
            $cacheConfigKey = 'cache.stores.redis.coders';
        } else {
            $cacheConfigKey = 'cache.coders';
        }

        $this->coderConfig = $config ?: Config::get($cacheConfigKey);

        if (!is_array($this->coderConfig)) {
            throw new Exception("You should configure coders as array at '$cacheConfigKey'");
        }
    }

    public function decode(array $value)
    {
        $coderClass = $value['coder'];

        if (!class_exists($coderClass)) {
            throw new Exception("Cannot load coder class '$coderClass'");
        }

        /** @var CoderInterface $coder */
        $coder = new $coderClass();
        $coder->setCoderManager($this);

        $decoded = $coder->decode($value['data']);

        return $decoded;
    }

    public function encode($value)
    {
        $coder = $this->getCoder($value);
        $coder->setCoderManager($this);

        $encoded = $coder->encode($value);

        $coderClass = get_class($coder);
        $this->assertEncodedValueValid($encoded, $coderClass);

        return ['coder' => $coderClass, 'data' => $encoded];
    }

    /**
     * @param mixed $value
     *
     * @return CoderInterface
     * @throws Exception
     */
    protected function getCoder($value)
    {
        foreach ($this->coderConfig as $valueClass => $coderData) {
            if (is_callable($coderData) && ($coderClass = $coderData($value))) {
                return $coderClass;
            }

            if (is_string($coderData) && ($value instanceof $valueClass)) {
                return new $coderData();
            }
        }

        throw new Exception("No coder found to encode value: ".serialize($value));
    }

    /**
     * @param $encoded
     * @param $coderClass
     *
     * @throws Exception
     */
    protected function assertEncodedValueValid($encoded, $coderClass)
    {
        if (!is_array($encoded)) {
            $serializedEncoded = serialize($encoded);
            throw new Exception(
                "Encoder '$coderClass' returned invalid data. Expected array, got '$serializedEncoded'"
            );
        }
    }
}
