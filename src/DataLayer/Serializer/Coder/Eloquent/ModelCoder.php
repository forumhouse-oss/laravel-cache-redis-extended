<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\Eloquent;

use Exception;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderInterface;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderManagerTrait;
use Illuminate\Database\Eloquent\Model;

class ModelCoder implements CoderInterface
{
    const FIELD_MODEL_CLASS = 'class';

    const FIELD_MODEL_ATTRIBUTES = 'attributes';

    const FIELD_MODEL_RELATIONS = 'relations';

    use CoderManagerTrait;

    /**
     * @param mixed $value
     *
     * @return Model
     * @throws Exception
     */
    public function decode($value)
    {
        $modelClass = $value[self::FIELD_MODEL_CLASS];

        if (!class_exists($modelClass)) {
            throw new Exception("Cannot instantiate model '$modelClass': cannot load class");
        }

        if (!is_subclass_of($modelClass, Model::class)) {
            throw new Exception("'$modelClass' is not a subclass of Eloquent model");
        }

        /** @var Model $model */
        $model = new $modelClass;
        $model->setRawAttributes($value[self::FIELD_MODEL_ATTRIBUTES], true);
        $model->exists = true;

        foreach ($value[self::FIELD_MODEL_RELATIONS] as $relationName => $relationValue) {
            $model->setRelation($relationName, $this->decodeAny($relationValue));
        }

        return $model;
    }

    /**
     * @param Model $value
     *
     * @return mixed
     */
    public function encode($value)
    {
        $result = [
            self::FIELD_MODEL_CLASS => get_class($value),
            self::FIELD_MODEL_ATTRIBUTES => $value->getAttributes(),
        ];

        $result[self::FIELD_MODEL_RELATIONS] = [];

        foreach ($value->getRelations() as $relationName => $relationData) {
            $result[self::FIELD_MODEL_RELATIONS][$relationName] = $this->encodeAny($relationData);
        }

        return $result;
    }
}
