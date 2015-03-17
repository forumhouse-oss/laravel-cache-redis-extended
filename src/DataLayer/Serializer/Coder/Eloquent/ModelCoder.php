<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\Eloquent;

use Exception;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderInterface;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderManagerTrait;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class ModelCoder implements CoderInterface
{
    const FIELD_MODEL_CLASS = 'class';

    const FIELD_MODEL_ATTRIBUTES = 'attributes';

    const FIELD_MODEL_ATTRIBUTES_ORIG = 'original';

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
        $attributes = $value[self::FIELD_MODEL_ATTRIBUTES];
        $model = $this->instantiateModel($modelClass, $value);
        $model->setRawAttributes($attributes, true);
        $this->setOriginalAttributes($value, $model);
        $model->exists = true;
        $this->coderManager->pushLastDecoded($model);

        foreach ($value[self::FIELD_MODEL_RELATIONS] as $relationName => $relationValue) {
            $model->setRelation($relationName, $this->decodeAny($relationValue));
        }
        $this->coderManager->popLastDecoded();

        return $model;
    }

    /**
     * @param Model $value
     *
     * @return mixed
     * @throws Exception
     */
    public function encode($value)
    {
        if (!$value instanceof Model) {
            throw new Exception("Cannot encode value of class '".get_class($value)."'");
        }

        $result = [
            self::FIELD_MODEL_CLASS => get_class($value),
            self::FIELD_MODEL_ATTRIBUTES => $value->getAttributes(),
            self::FIELD_MODEL_ATTRIBUTES_ORIG => $this->getOriginalAttributes($value),
        ];

        $result[self::FIELD_MODEL_RELATIONS] = [];

        foreach ($value->getRelations() as $relationName => $relationData) {
            $result[self::FIELD_MODEL_RELATIONS][$relationName] = $this->encodeAny($relationData);
        }

        $result = $this->addCustomData($result, $value);

        return $result;
    }

    /**
     * @param string $modelClass
     * @param array  $data
     *
     * @return mixed
     */
    protected function instantiateModel($modelClass, array $data)
    {
        return new $modelClass;
    }

    /**
     * @param array       $data
     * @param array|Model $value
     *
     * @return array
     */
    protected function addCustomData(array $data, Model $value)
    {
        return $data;
    }

    /**
     * @param $data
     * @param $model
     */
    protected function setOriginalAttributes($data, Model $model)
    {
        //TODO: store only diff with raw attributes
        $attributesOrig = $data[self::FIELD_MODEL_ATTRIBUTES_ORIG];
        $reflection = new ReflectionClass($model);
        $property = $reflection->getProperty("original");
        $property->setAccessible(true);
        $property->setValue($model, $attributesOrig);
        $property->setAccessible(false);
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function getOriginalAttributes(Model $value)
    {
        //TODO: store only diff with raw attributes
        return $value->getOriginal();
    }
}
