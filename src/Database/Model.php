<?php
namespace Pho\Database;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel {
    public static $key_types = [];
    
    public const KEY_TYPE_RAW = 1;
    public const KEY_TYPE_MUTATOR = 2;
    public const KEY_TYPE_CAST = 3;
    public const KEY_TYPE_DATETIME = 4;

    protected function getTypeByKey(string $key)
    {
        $key = static::class.'::'.$key;

        if (!isset(static::$key_types[$key])) {
            if ($this->hasGetMutator($key)) {
                static::$key_types[$key] = static::KEY_TYPE_MUTATOR;
            } elseif ($this->hasCast($key)) {
                static::$key_types[$key] = static::KEY_TYPE_CAST;
            } elseif (in_array($key, $this->getDates())) {
                static::$key_types[$key] = static::KEY_TYPE_DATETIME;
            } else {
                static::$key_types[$key] = static::KEY_TYPE_RAW;
            }
        }

        return static::$key_types[$key];
    }

    public function getAttribute($key)
    {
        if (!$key) {
            return;
        }

        $type = $this->getTypeByKey($key);

        if (array_key_exists($key, $this->attributes) ||
            $type === static::KEY_TYPE_MUTATOR) {
            return $this->getAttributeValue($key, $type);
        }

        if (method_exists(\Illuminate\Database\Eloquent\Model::class, $key)) {
            return;
        }

        return $this->getRelationValue($key);
    }

    public function getAttributeValue($key, $type = null)
    {
        $type = $type ?: $this->getTypeByKey($key);
        $value = $this->getAttributeFromArray($key);

        if ($type === static::KEY_TYPE_MUTATOR) {
            return $this->mutateAttribute($key, $value);
        } elseif ($type === static::KEY_TYPE_CAST) {
            return $this->castAttribute($key, $value);
        } elseif ($type === static::KEY_TYPE_DATETIME && !is_null($value)) {
            return $this->asDateTime($value);
        }

        return $value;
    }
}