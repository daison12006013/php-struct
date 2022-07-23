<?php

declare(strict_types=1);

namespace Daison\Struct;

use ArrayAccess;
use RuntimeException;
use ReflectionFunction;
use Closure;
use InvalidArgumentException;
use ReflectionType;
use ReflectionNamedType;

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
        if (!isset($this->dataTypes[$name])) {
            throw new RuntimeException("Struct: Undefined struct data [$name]");
        }

        if (!defined('STRUCT_PARAM_CHECKING') || STRUCT_PARAM_CHECKING) {
            $this->strictParameterChecking($name);
        }

        return call_user_func($this->dataTypes[$name], $this->data[$name]);
    }

    protected function strictParameterChecking(string $name)
    {
        $value = $this->data[$name];
        $ref = new ReflectionFunction($this->dataTypes[$name]);
        $paramTypes = $this->resolveClosureParams($ref);
        $valueType = gettype($value);
        $returnType = $ref->getReturnType();

        // rewrite the value type if it is an object
        // we want to compare the full class of it
        if ($valueType === 'object') {
            $valueType = get_class($value);
        }

        $valueToStr = $this->transformToString($value);

        if (!empty($paramTypes[0]) && ! $this->isTypeEqual($paramTypes[0], $valueType)) {
            throw new InvalidArgumentException("Struct: Data type of [$name] expects [{$paramTypes[0]}] but value is $valueToStr typed [$valueType]");
        }

        if ($returnType instanceof ReflectionType) {
            $returnType = (string) $returnType;

            if (! $this->isTypeEqual($returnType, $valueType)) {
                throw new InvalidArgumentException("Struct: Return type of [$name] expects [$returnType] but value is $valueToStr typed [$valueType]");
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
     * Check if both param are equal
     *
     * @param string $first
     * @param string $second
     *
     * @return bool
     */
    protected function isTypeEqual(string $first, string $second)
    {
        $maps = [
            'int'   => 'integer',
            'bool'  => 'boolean',
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
