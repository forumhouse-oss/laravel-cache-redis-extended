<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer;

/**
 * Entrance class into serialization
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Coder
 */
interface CoderManagerInterface
{
    /**
     * @param array $value
     *
     * @return mixed
     */
    public function decode(array $value);

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function encode($value);

    /**
     * Coder manager should maintain a stack of decoded values
     * for coders to be able reconstruct links from child to parent
     *
     * @param int $index
     *
     * @return mixed
     */
    public function getLastDecoded($index = 0);

    /**
     * Coder manager should maintain a stack of encoded values
     * for coders to be able reconstruct links from parent to child
     *
     * @param int $index
     *
     * @return mixed
     */
    public function getLastEncoded($index = 0);

    /**
     * @param array $value
     */
    public function pushLastEncoded($value);

    /**
     * @param mixed $value
     */
    public function pushLastDecoded($value);

    /**
     * @return void
     */
    public function popLastEncoded();

    /**
     * @return void
     */
    public function popLastDecoded();
}
