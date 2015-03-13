<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serialization\Coder;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class responsible for encoding / decoding Eloquent models and collections
 *
 * @package FHTeam\LaravelRedisCache\DataLayer\Serialization\Coder
 */
class EloquentCoder implements CoderInterface
{

    const TYPE_MODEL = 'model';

    const TYPE_COLLECTION = 'collection';

    const FIELD_TYPE = 'type';

    const FIELD_MODEL_CLASS = 'class';

    const FIELD_MODEL_ATTRIBUTES = 'attributes';

    const FIELD_COLLECTION_ITEMS = 'items';

    const FIELD_MODEL_RELATIONS = 'relations';

    public function decode($value)
    {
        if (!isset($value[self::FIELD_TYPE])) {
            throw new Exception("Attempt to deserialize damaged object (no 'type' data): " . json_encode($value));
        }

        switch ($value[self::FIELD_TYPE]) {
            case self::TYPE_MODEL:
                return $this->decodeModel($value);
                break;
            case self::TYPE_COLLECTION:
                return $this->decodeCollection($value);
                break;
            default:
                throw new Exception("Unable to deserialize type '{$value[self::FIELD_TYPE]}'");
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
     * Encodes Eloquent model into array of data
     *
     * @param Model $model
     *
     * @return array
     * @throws Exception
     */
    protected function encodeModel(Model $model)
    {
        $result = [
            self::FIELD_TYPE => self::TYPE_MODEL,
            self::FIELD_MODEL_CLASS => get_class($model),
            self::FIELD_MODEL_ATTRIBUTES => $model->getAttributes(),
        ];

        foreach ($model->getRelations() as $relationName => $relationData) {
            $result[self::FIELD_MODEL_RELATIONS][$relationName] = $this->encode($relationData);
        }

        return $result;
    }

    /**
     * Encodes Eloquent collection into array of data
     *
     * @param Collection $collection
     *
     * @return array
     * @throws Exception
     */
    protected function encodeCollection(Collection $collection)
    {
        $result = [
            self::FIELD_TYPE => self::TYPE_COLLECTION,
            self::FIELD_COLLECTION_ITEMS => [],
        ];
        foreach ($collection as $item) {
            $result[self::FIELD_COLLECTION_ITEMS][] = $this->encode($item);
        }

        return $result;
    }

    /**
     * Decodes Eloquent model from array of data
     *
     * @param $value
     *
     * @return Model
     * @throws Exception
     */
    protected function decodeModel(array $value)
    {
        $modelClass = $value[self::FIELD_MODEL_CLASS];
        /** @var Model $model */
        $model = new $modelClass;
        $model->setRawAttributes($value[self::FIELD_MODEL_ATTRIBUTES], true);
        $model->exists = true;

        foreach ($value[self::FIELD_MODEL_RELATIONS] as $relationName => $relationValue) {
            $model->setRelation($relationName, $this->decode($relationValue));
        }
        return $model;
    }

    /**
     * Decodes Eloquent collection from array of data
     *
     * @param array $value
     *
     * @return Collection
     * @throws Exception
     */
    protected function decodeCollection(array $value)
    {
        if (!isset($value[self::FIELD_COLLECTION_ITEMS])) {
            throw new Exception("Attempt to deserialize damaged collection (no 'items' data): " . json_encode($value));
        }

        $result = [];
        foreach ($value[self::FIELD_COLLECTION_ITEMS] as $item) {
            $result[] = $this->decode($item);
        }

        return new Collection($result);
    }
}
