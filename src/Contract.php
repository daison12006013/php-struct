<?php

declare(strict_types = 1);

namespace Daison\Struct;

/**
 * @method self load(array $data) load the data
 * @method array toArray() return the array along with the missing keys
 */
interface Contract
{
    public function load($data);
    public function toArray(): array;
}
