<?php namespace FHTeam\LaravelRedisCache\DataLayer\Coder\Eloquent;

use Exception;
use FHTeam\LaravelRedisCache\DataLayer\Coder\CoderInterface;
use FHTeam\LaravelRedisCache\DataLayer\Coder\CoderManagerTrait;
use Illuminate\Database\Eloquent\Model;

class ModelCoder implements CoderInterface
{
    const FIELD_MODEL_CLASS = 'class';

    const FIELD_MODEL_ATTRIBUTES = 'attributes';

    const FIELD_MODEL_ATTRIBUTES_ORIG = 'original';

    const FIELD_MODEL_RELATIONS = 'relations';

    const FIELD_EXISTS = 'exists';

    use CoderManagerTrait;

    /**
     * @param mixed $value
     *
     * @return Model
     * @throws Exception
     */
    public function decode($value)
    {
        return unserialize($value['data']);
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

        return ['data' => serialize($value)];
    }
}
