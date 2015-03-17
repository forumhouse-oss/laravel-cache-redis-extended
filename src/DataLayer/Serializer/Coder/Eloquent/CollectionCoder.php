<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\Eloquent;

use Exception;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderInterface;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderManagerTrait;
use Illuminate\Database\Eloquent\Collection;

class CollectionCoder implements CoderInterface
{
    const FIELD_COLLECTION_ITEMS = 'items';

    use CoderManagerTrait;

    /**
     * @param mixed $value
     *
     * @return Collection
     * @throws Exception
     */
    public function decode($value)
    {
        if (!isset($value[self::FIELD_COLLECTION_ITEMS])) {
            throw new Exception("Attempt to deserialize damaged collection (no 'items' data): ".json_encode($value));
        }

        $result = [];
        foreach ($value[self::FIELD_COLLECTION_ITEMS] as $item) {
            $result[] = $this->decodeAny($item);
        }

        return new Collection($result);
    }

    /**
     * @param Collection $value
     *
     * @return mixed
     */
    public function encode($value)
    {
        $result = [
            self::FIELD_COLLECTION_ITEMS => [],
        ];
        foreach ($value as $item) {
            $result[self::FIELD_COLLECTION_ITEMS][] = $this->encodeAny($item);
        }

        return $result;
    }
}
