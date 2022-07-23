PHP 7.4 [![PHP Composer](https://github.com/daison12006013/php-struct/actions/workflows/php7.4.yml/badge.svg)](https://github.com/daison12006013/php-struct/actions/workflows/php7.4.yml)

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

## Checklists

- [x] Struct-uring
- [x] Thrown strict types
  - [x] Data type is different from the value returned
  - [x] Returned type is different from the value returned
- [ ] Validation (in progress...)
