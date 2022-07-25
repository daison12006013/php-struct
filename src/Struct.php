<?php

declare(strict_types=1);

namespace Daison\Struct;

use ReflectionFunction;
use ReflectionType;

class Struct implements Contract
{
    private array $dataTypes;
    private $data;

    public function __construct(array $dataTypes)
    {
        $this->dataTypes = $dataTypes;
    }

    public function load($data)
    {
        $this->data = $data;

        return $this;
    }

    public function toArray(): array
    {
        $arr = [];

        foreach ($this->dataTypes as $key => $val) {
            $resolved = $this->{$key}($val);

            if ($resolved instanceof Collection) {
                $resolved = $resolved->toArray();
            }

            $arr[$key] = $resolved;
        }

        return $arr;
    }

    public function __call(string $name, array $args = [])
    {
        return $this->findKeyAndCall($name);
    }

    /**
     * Automatically resolve a variable
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->findKeyAndCall($name);
    }

    /**
     * Find the array key and invoke the callback.
     *
     * @param  string $name
     * @return void
     */
    protected function findKeyAndCall(string $name)
    {
        if (!isset($this->dataTypes[$name])) {
            throw new TypeException("Struct: Undefined struct data [$name]");
        }

        if (!defined('STRUCT_PARAM_CHECKING') || STRUCT_PARAM_CHECKING) {
            $this->strictParameterChecking($name);
        }

        if (! isset($this->data[$name])) {
            return null;
        }

        return call_user_func($this->dataTypes[$name], $this->data[$name]);
    }

    /**
     * Check params and returns.
     *
     * @param string $name
     * @return void
     * @throws TypeException
     */
    protected function strictParameterChecking(string $name)
    {
        $ref = new ReflectionFunction($this->dataTypes[$name]);
        $paramTypes = $this->resolveClosureParams($ref);
        $returnType = $ref->getReturnType();

        if ($returnType === null) {
            return null;
        }

        $value = $this->data[$name];
        $valueType = gettype($value);

        // rewrite the value type if it is an object
        // we want to compare the full class of it
        if ($valueType === 'object') {
            $valueType = get_class($value);
        }

        $valueToStr = $this->transformToString($value);

        if (!empty($paramTypes[0]) && !$this->isTypeEqual($paramTypes[0], $valueType)) {
            throw new TypeException("Struct: Data type of [$name] expects [{$paramTypes[0]}] but value is $valueToStr typed [$valueType]");
        }

        if ($returnType instanceof ReflectionType) {
            $returnType = (string) $returnType;

            if (!$this->isTypeEqual($returnType, $valueType)) {
                throw new TypeException("Struct: Return type of [$name] expects [$returnType] but value is $valueToStr typed [$valueType]");
            }
        }
    }

    protected function transformToString($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Check if both param are equal.
     *
     * @return bool
     */
    protected function isTypeEqual(string $first, string $second)
    {
        $maps = [
            'int' => 'integer',
            'bool' => 'boolean',
            'float' => 'double',
        ];

        $first = isset($maps[$first]) ? $maps[$first] : $first;
        $second = isset($maps[$second]) ? $maps[$second] : $second;

        return $first === $second;
    }

    protected function resolveClosureParams(ReflectionFunction $ref): array
    {
        $params = $ref->getParameters();

        return array_map(function ($param) {
            $type = $param->getType();

            if ($type instanceof ReflectionType) {
                return $param->getType()->getName();
            }
        }, $params);
    }
}
