<?php

declare(strict_types=1);

namespace Daison\Struct;

use ArrayAccess;
use Iterator;

class Collection implements ArrayAccess, Iterator
{
    use ArrayAccessTrait;
    use IteratorTrait;

    private array $data = [];
    private Contract $struct;

    public function __construct(Contract $struct, array $appends = [])
    {
        $this->struct = $struct;

        array_map(fn ($val) => $this->append($val), $appends);
    }

    /**
     * Append any value.
     *
     * @param mixed $value
     * @return self
     */
    public function append($value)
    {
        $this->data[] = (clone $this->struct)->load($value);

        return $this;
    }

    /**
     * Check if the data is empty.
     */
    public function empty(): bool
    {
        return empty($this->data);
    }

    /**
     * Transform data into array
     *
     * @return array
     */
    public function toArray(): array
    {
        $arr = [];

        /** @var Contract */
        foreach ($this->data as $idx => $datum) {
            $arr[$idx] = $datum->toArray();
        }

        return $arr;
    }
}
