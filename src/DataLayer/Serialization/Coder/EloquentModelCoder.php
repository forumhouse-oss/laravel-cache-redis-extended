<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serialization\Coder;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class responsible for encoding / decoding Eloquent models
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Serialization\Coder
 */
class EloquentModelCoder implements CoderInterface
{

    const TYPE_MODEL = 'model';
    const TYPE_COLLECTION = 'collection';

    public function decode($value)
    {
        if (!isset($value['type'])) {
            throw new Exception("Attempt to deserialize damaged object (no 'type' data): " . json_encode($value));
        }

        switch ($value['type']) {
            case self::TYPE_MODEL:
                return $this->decodeModel($value);
                break;
            case self::TYPE_COLLECTION:
                return $this->decodeCollection($value);
                break;
            default:
                throw new Exception("Unable to deserialize type '{$value['type']}'");
        }
    }

    public function encode($value)
    {
        if ($value instanceof Collection) {
            return $this->encodeCollection($value);
        }

        if ($value instanceof Model) {
            return $this->encodeModel($value);
        }

        throw new Exception("Unable to serialize data of class " . get_class($value));
    }

    /**
     * @param Model $model
     *
     * @return array
     * @throws Exception
     */
    protected function encodeModel(Model $model)
    {
        $result = [
            'type' => self::TYPE_MODEL,
            'class' => get_class($model),
            'attributes' => $model->getAttributes(),
        ];

        foreach ($model->getRelations() as $relationName => $relationData) {
            if ($relationData instanceof Collection) {
                $result['relations'][$relationName] = $this->encodeCollection($relationData);
                continue;
            }

            if ($relationData instanceof Model) {
                $result['relations'][$relationName] = $this->encode($relationData);
                continue;
            }

            throw new Exception("Unable to serialize data of class " . get_class($relationData));
        }

        return $result;
    }

    /**
     * @param Collection $collection
     *
     * @return array
     * @throws Exception
     */
    protected function encodeCollection(Collection $collection)
    {
        $result = [
            'type' => self::TYPE_COLLECTION,
            'items' => [],
        ];
        foreach ($collection as $item) {
            $result['items'][] = $this->encodeModel($item);
        }

        return $result;
    }

    protected function decodeModel($value)
    {
    }

    protected function decodeCollection(array $value)
    {
        if (!isset($value['items'])) {
            throw new Exception("Attempt to deserialize damaged collection (no 'items' data): " . json_encode($value));
        }

        $result = [];
        foreach ($value['items'] as $item) {
            $result[] = $this->decodeModel($item);
        }

        return new \Illuminate\Database\Eloquent\Collection($result);
    }
}
