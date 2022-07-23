<?php

declare(strict_types=1);

namespace Daison\Struct;

trait ArrayAccessTrait
{
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset): bool
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}
