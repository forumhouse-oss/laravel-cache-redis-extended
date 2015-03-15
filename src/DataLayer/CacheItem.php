<?php

namespace FHTeam\LaravelRedisCache\DataLayer;

/**
 * Handles wrapping and unwrapping values, that are stored into cache
 *
 * @package FHTeam\LaravelRedisCache\Core
 */
class CacheItem
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
     * @var int
     */
    protected $expires;

    /**
     * @var array<string, int>
     */
    protected $tags;

    /**
     * @var string
     */
    protected $value;

    /**
     * @param mixed $value
     * @param int   $ttl
     * @param array $tags
     *
     * @return CacheItem
     */
    public static function encode($value, $ttl, array $tags = [])
    {
        $obj = new static();
        $obj->setValue($value);
        $obj->setExpires(time() + $ttl);
        $obj->setTags($tags);

        return $obj;
    }

    /**
     * @param string $data
     *
     * @return null|CacheItem
     */
    public static function decode($data)
    {
        if (null === $data) {
            return null;
        }

        $data = unserialize($data);

        $obj = new static();
        $obj->setValue($data[self::IDX_RAW_VALUE]);
        $obj->setExpires($data[self::IDX_EXPIRES]);
        $obj->setTags($data[self::IDX_TAGS]);

        return $obj;
    }

    /**
     * Serializes object to a string representation
     *
     * @return string
     */
    public function __toString()
    {
        return serialize([
            self::IDX_RAW_VALUE => $this->getValue(),
            self::IDX_EXPIRES => $this->getExpires(),
            self::IDX_TAGS => $this->getTags(),
        ]);
    }

    public function isExpired()
    {
        return $this->expires < time();
    }

    /**
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param int $expires
     *
     * @return $this
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     *
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
