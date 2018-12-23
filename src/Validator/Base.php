<?php
namespace Pho\Validator;

use Respect\Validation\Validator as v;

abstract class Base
{
    public static function validator($validatorName, $requiredKeys = [], $optionalKeys = [])
    {
        $rules = call_user_func_array([static::class, $validatorName], []);
        $args = [];

        if ($requiredKeys) {
            foreach ($rules as $key => $rule) {
                $args[] = v::keyNested($key, $rule, in_array($key, $requiredKeys));
            }
        } else {
            foreach ($rules as $key => $rule) {
                $args[] = v::keyNested($key, $rule, !in_array($key, $optionalKeys));
            }
        }

        return call_user_func_array([v::class, 'allOf'], $args);
    }
}
