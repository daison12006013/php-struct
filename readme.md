PHP Version | Status
------------|--------
7.4 | [![PHP 7.4 Composer](https://github.com/daison12006013/php-struct/actions/workflows/php7.4.yml/badge.svg)](https://github.com/daison12006013/php-struct/actions/workflows/php7.4.yml)
8.x | in progress...

# PHP Struct

A new way to structure your data, apply a real strict typing to your team, avoid any unwanted types!

Inspired by Go (golang)

## Example

```php

use Daison\Struct\Collection;
use Daison\Struct\Struct;

$photoStruct = new Struct([
    'name' => fn ($name): string => $name,
    'url' => fn ($url): string => $url,
]);

$userStruct = new Struct([
    'firstName' => fn (string $firstName): string => $firstName,
    'photos' => fn (array $photos) => new Collection($photoStruct, $photos ?? []),
    'age' => fn (int $age): int => $age,
]);

$userStruct->load([
    'firstName' => 'John',
    'age' => '31',
    'photos' => [
        ['url' => 'https://images.dummy.com/x.jpg'],
        ['url' => 'https://images.dummy.com/y.jpg'],
        ['url' => 'https://images.dummy.com/z.jpg'],
    ],
]);

$userStruct->firstName(); // John
$userStruct->photos()->empty(); // false
$userStruct->photos()[0]->url(); // https://images.dummy.com/x.jpg

$userStruct->age(); // Struct: Data type of [age] expects [int] but value is '31' typed [string]
$userStruct->email(); // Struct: Undefined struct data [email]
```

A full examples can be seen inside [example.php](example.php) or by running `./run-tests.sh`

## Type Hinting

For your IDE's or VSCode "PHP Intelephense" or similar.
Type hinting helps you to show a lists of available methods.

```php
/**
 * @method string url()
 * @method string name()
 */
interface TypePhotoInterface extends Contract
{
    // avoid filling this up, the purpose of this interface
    // is only to support your IDE / Code Editor
}
```

Let's determine above code, as you can see I've created an interface `TypePhotoInterface` and added a 2 methods `url()` and `name()` [php docblock](https://docs.phpdoc.org/2.9/references/phpdoc/tags/method.html#:~:text=The%20%40method%20tag%20allows%20the,case%20'void'%20is%20implied.)

```php
/** @var TypePhotoInterface */
$photoStruct = new Struct([
    'name' => fn (string $name): string => $name,
    'url' => fn (string $url): string => $url,
]);
```

Above code, we are referencing the interface into `$photoStruct` variable. So basically when you're going to type this `$photoStruct->` into your code editor, it will just basically show the lists of methods available.

## Checklists

- [x] Struct-uring
- [x] Thrown strict types
  - [x] Data type is different from the value returned
  - [x] Returned type is different from the value returned
- [ ] Validation (in progress...)
