<?php

declare(strict_types=1);

namespace Daison\Struct;

use Closure;

/**
 * This class provides common types.
 */
class Common
{
    public static function ANY(): Closure
    {
        return fn ($val) => $val;
    }

    public static function INTEGER(): Closure
    {
        return fn (int $val): int => $val;
    }

    public static function STRING(): Closure
    {
        return fn (string $val): string => $val;
    }

    public static function FLOAT(): Closure
    {
        return fn (float $val): float => $val;
    }

    public static function BOOLEAN(): Closure
    {
        return fn (bool $val): bool => $val;
    }
}
