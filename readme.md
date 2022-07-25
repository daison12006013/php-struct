PHP Version | Status
------------|--------
7.4 | [![PHP 7.4 Composer](https://github.com/daison12006013/php-struct/actions/workflows/php7.4.yml/badge.svg)](https://github.com/daison12006013/php-struct/actions/workflows/php7.4.yml)
8.x | in progress...

- [PHP Struct](#php-struct)
  - [Quick Example](#quick-example)
  - [Struct over Struct](#struct-over-struct)
  - [Optional Keys](#optional-keys)
  - [Checklists](#checklists)
  - [License](#license)

# PHP Struct

A new way to structure your data, apply a real strict typing to your team, avoid any

- Unwanted types, such as you're expecting it to be `int` while your FE / Client returns with `string` quoted value.
- This helps you to avoid using too much `isset($data['key'])` or `!empty(...)`
  - The array key is always present when calling `->toArray()`

Inspired by Go(golang)'s struct

## Quick Example

We do it this way in golang, where we transform a json request going to the server.

```go
type Photo struct {
    name string
    url  string
}
type User struct {
    firstName string
    age       int
    photos    []Photo
}

var user User
jsonStr := `{...}`
json.Unmarshal([]byte(jsonStr), &user)

fmt.Print(user.firstName)
fmt.Print(user.age)
fmt.Printf("%+v", user.photos)
```

Now if we're going to convert that into this library.

```php
use Daison\Struct\Collection;
use Daison\Struct\Common;
use Daison\Struct\TypeException;
use Daison\Struct\Struct;

$photoStruct = new Struct([
    'name' => Common::STRING(),
    'url' => Common::STRING(),
]);

$userStruct = new Struct([
    'firstName' => Common::STRING(),
    'age' => Common::INTEGER(),
    'photos' => fn (array $photos) => new Collection($photoStruct, $photos ?? []),
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

try {
    $userStruct->firstName; // John
    $userStruct->photos->empty(); // false
    $userStruct->photos[0]->url; // https://images.dummy.com/x.jpg

    // TypeException -> "Struct: Data type of [age] expects [int] but value is '31' typed [string]"
    $userStruct->age;

    // TypeException -> "Struct: Undefined struct data [email]"
    $userStruct->email;
} catch (TypeException $e) {
    // log it somewhere thru your kibana / sentry
    var_dump($e);

    throw $e;
}
```

A full examples can be seen inside [example.php](example.php) or by running `./run-tests.sh`

## Struct over Struct

```php
// imagine this is your contract structure from your frontend / clients
$load = [
    'location' => ['x' => 0.111, 'y' => 0.555],
];

$location = new Struct([
    'x' => Common::FLOAT(),
    'y' => Common::FLOAT(),
]);

$sample = new Struct([
    'location' => fn (array $d) => $location->load($d),
]);

$sample->load($load);
$sample->location->x; // 0.111
$sample->location->y; // 0.555
```

## Optional Keys

Optional keys will still resolve the value as null, this acts the same in Golang interface{} for nil'ishable approach

```php
// imagine this is your contract structure from your frontend / clients
$load = [
    // basically this data is not required
    // 'nullableKey' => 'whatever',

    'requiredKey' => 1,
];

$sample = new Struct([
    // if there is no return type, this library interprets it as optional or any type.
    'nullableKey' => fn ($x) => $x,

    // since we require it to be "string", the library can detect and throw
    // TypeException if the provided data is missing or the type is wrong
    'requiredKey' => fn (string $age) => string $age,
]);
$sample->load($load);
$sample->toArray(); // ['nullableKey' => null, 'requiredKey' => 1]
```

<!--
## Type Hinting (optional)

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

-->

## Checklists

- [x] Struct-uring
- [x] Thrown strict types
  - [x] Data type is different from the value returned
  - [x] Returned type is different from the value returned
- [ ] Validation (in progress...)

## License

The php-struct library is open-sourced software licensed under the [MIT license.](/license.md)
