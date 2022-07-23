<?php

define('STRUCT_PARAM_CHECKING', true);
error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__.'/vendor/autoload.php';

use Daison\Struct\Collection;
use Daison\Struct\Contract;
use Daison\Struct\Struct;

/**
 * @method string     email()     Get the email
 * @method string     firstName() Get the first name
 * @method string     lastName()  Get the last name
 * @method string     gender()    Get the gender
 * @method int        age()       Get the age
 * @method Collection photos()    Get the lists of photos
 * @method bool       married()
 * @method array      location()
 * @method Closure    closure()
 * @method DummyClass class()
 */
interface UserStruct extends Contract
{
}

/**
 * @method string url()
 * @method string name()
 */
interface PhotoStruct extends Contract
{
}

/** @var PhotoStruct */
$photoStruct = new Struct([
    'name' => fn ($name): string => $name,
    'url' => fn ($url): string => $url,
]);

/** @var UserStruct */
$userStruct = new Struct([
    'email' => fn (string $email): string => $email,
    'firstName' => fn (string $firstName): string => $firstName,
    'lastName' => fn (string $lastName): string => $lastName,
    'gender' => fn (string $gender): string => $gender,
    'age' => fn (int $age): int => $age,
    'married' => fn (bool $married): bool => $married,
    'location' => fn (array $location): array => $location,
    'photos' => fn (array $photos) => new Collection($photoStruct, $photos ?? []),
    'closure' => fn (Closure $x): Closure => $x,
    'class' => fn (DummyClass $x): DummyClass => $x,
]);

class DummyClass
{
}

$userStruct->load([
    'email' => 'johndoe@email.com',
    'firstName' => 'John',
    'lastName' => 'Doe',
    'gender' => 'male',
    'age' => 31,
    'married' => true,
    'location' => ['x' => 0.111, 'y' => 0.555],
    'photos' => [
        [
            'name' => 'GitHub',
            'url' => 'https://github.com/daison12006013',
            'no-reference-should-not-be-added' => '',
        ],
    ],
    'closure' => fn () => true,
    'class' => new DummyClass(),
]);

var_dump([
    'email' => $userStruct->email(),
    'firstName' => $userStruct->firstName(),
    'lastName' => $userStruct->lastName(),
    'gender' => $userStruct->gender(),
    'age' => $userStruct->age(),
    'photos' => $userStruct->photos(),
    'married' => $userStruct->married(),
    'location' => $userStruct->location(),
    'photos_is_empty' => $userStruct->photos()->empty(),
    'toArray' => $userStruct->toArray(),
    'closure' => $userStruct->closure(),
    'class' => $userStruct->class(),
]);

/** @var PhotoStruct */
foreach ($userStruct->photos() as $idx => $photo) {
    var_dump($idx, $photo->name());
    var_dump($idx, $photo->url());

    // expect that this will throw an error, unless it is part of the loaded struct
    // var_dump($idx, $photo->whatever());
}
