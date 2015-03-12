<?php

namespace FHTeam\LaravelRedisCache\DataLayer;

use FHTeam\LaravelRedisCache\Core\TagManager;
use FHTeam\LaravelRedisCache\DataLayer\Coder\CoderManager;
use Illuminate\Redis\Database;

/**
 * Handles wrapping and unwrapping values, that are stored into cache
 *
 * @package FHTeam\LaravelRedisCache\Core
 */
class Wrapper
{
    /*
     * Fields of the serialized object in cache
     */

    /** When this object expire: int */
    const IDX_EXPIRES = 'expires';

    /** Object tags: string name => int version */
    const IDX_TAGS = 'tags';

    /** Object value: mixed */
    const IDX_RAW_VALUE = 'value';

    /**
     * @var TagManager
     */
    protected $tagManager;

    /**
     * @var CoderManager
     */
    protected $coder;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var Database
     */
    protected $redis;

    /**
     * @param Database   $redis
     * @param TagManager $tagManager
     * @param string     $prefix
     */
    public function __construct(Database $redis, $tagManager, $prefix)
    {
        $this->tagManager = $tagManager;
        $this->coder = new CoderManager();
        $this->prefix = $prefix;
        $this->redis = $redis;
    }

    public function unwrapData($values)
    {
        $result = [];
        foreach ($values as $key => $val) {
            $result[$this->prefix . $key] = $this->unwrapValue($val);
        }
        return $result;
    }

    /**
     * @param array $values
     * @param int   $seconds
     * @param array $tags
     *
     * @return array
     */
    public function wrapData(array $values, $seconds, array $tags)
    {
        $result = [];
        foreach ($values as $key => $val) {
            $result[$this->prefix . $key] = $this->wrapValue($val, $seconds, $tags);
        }
        return $result;
    }

    /**
     * @param string $rawValue
     *
     * @return null|mixed
     */
    protected function unwrapValue($rawValue)
    {
        if (null === $rawValue) {
            return null;
        }

        $rawValue = unserialize($rawValue);

        //is cache itself expired?
        if ($rawValue[self::IDX_EXPIRES] < time()) {
            return null;
        }

        if ($this->tagManager->anyTagExpired($rawValue[self::IDX_TAGS])) {
            return null;
        }

        $value = $this->coder->decode($rawValue[self::IDX_RAW_VALUE]);

        return $value;
    }

    /**
     * @param mixed $value
     * @param int   $seconds
     *
     * @return mixed
     */
    protected function wrapValue($value, $seconds, $tags)
    {
        $data = [
            self::IDX_EXPIRES => $seconds,
            self::IDX_TAGS => $this->tagManager->getActualTagVersions($tags),
            self::IDX_RAW_VALUE => $this->coder->encode($value),
        ];

        return serialize($data);
    }
}
