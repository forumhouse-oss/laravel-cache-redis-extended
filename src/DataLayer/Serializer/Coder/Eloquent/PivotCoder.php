<?php

namespace FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\Eloquent;

use Exception;
use FHTeam\LaravelRedisCache\DataLayer\Serializer\Coder\CoderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PivotCoder extends ModelCoder implements CoderInterface
{
    const FIELD_TABLE_NAME = 'table_name';
    const FIELD_FOREIGN_KEY = 'foreign_key';
    const FIELD_OTHER_KEY = 'other_key';

    public function decode($value)
    {
        return parent::decode($value);
    }

    public function encode($value)
    {
        if (!$value instanceof Pivot) {
            throw new Exception("Cannot encode value of class '".get_class($value)."'");
        }

        return parent::encode($value);
    }


    protected function instantiateModel($modelClass, array $data)
    {
        /** @var Pivot $model */
        $parent = $this->coderManager->getLastDecoded(1);

        $model = new $modelClass(
            $parent,
            $data[self::FIELD_MODEL_ATTRIBUTES],
            $data[self::FIELD_TABLE_NAME],
            true
        );

        $model->setPivotKeys($data[self::FIELD_FOREIGN_KEY], $data[self::FIELD_OTHER_KEY]);

        return $model;
    }

    protected function addCustomData(array $data, Model $value)
    {
        /** @var Pivot $value */
        $data[self::FIELD_TABLE_NAME] = $value->getTable();
        $data[self::FIELD_FOREIGN_KEY] = $value->getForeignKey();
        $data[self::FIELD_OTHER_KEY] = $value->getOtherKey();

        return $data;
    }
}
