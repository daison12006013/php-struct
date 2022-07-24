<?php

define('STRUCT_PARAM_CHECKING', true);
error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__.'/vendor/autoload.php';

use Daison\Struct\Collection;
use Daison\Struct\Contract;
use Daison\Struct\Struct;

/**
 * @method string             email()     Get the email
 * @method string             firstName() Get the first name
 * @method string             lastName()  Get the last name
 * @method string             gender()    Get the gender
 * @method int                age()       Get the age
 * @method Collection         photos()    Get the lists of photos
 * @method bool               married()
 * @method TypeLocationStruct location()
 * @method Closure            closure()
 * @method DummyClass         class()
 */
interface TypeUserStruct extends Contract
{
    // avoid filling this up, the purpose of this interface
    // is only to support your IDE / Code Editor
}

/**
 * @method string url()
 * @method string name()
 */
interface TypePhotoStruct extends Contract
{
    // avoid filling this up, the purpose of this interface
    // is only to support your IDE / Code Editor
}

/**
 * @method float x() Get the x axis
 * @method float y() Get the y axis
 */
interface TypeLocationStruct extends Contract
{
    // avoid filling this up, the purpose of this interface
    // is only to support your IDE / Code Editor
}

$locationStruct = new Struct([
    'x' => fn (float $x): float => $x,
    'y' => fn (float $y): float => $y,
]);

/** @var TypePhotoStruct */
$photoStruct = new Struct([
    'name' => fn (string $name): string => $name,
    'url' => fn (string $url): string => $url,
]);

/** @var TypeUserStruct */
$userStruct = new Struct([
    'email' => fn (string $email): string => $email,
    'firstName' => fn (string $firstName): string => $firstName,
    'lastName' => fn (string $lastName): string => $lastName,
    'gender' => fn (string $gender): string => $gender,
    'age' => fn (int $age): int => $age,
    'married' => fn (bool $married): bool => $married,
    'location' => fn (array $location) => $locationStruct->load($location),
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
    'married' => $userStruct->married(),
    'location' => $userStruct->location(),
    'location_x' => $userStruct->location()->x(),
    'location_y' => $userStruct->location()->y(),
    'photos' => $userStruct->photos(),
    'photos_0' => $userStruct->photos()[0],
    'photos_0_name' => $userStruct->photos()[0]->name(),
    'photos_is_empty' => $userStruct->photos()->empty(),
    'toArray' => $userStruct->toArray(),
    'closure' => $userStruct->closure(),
    'class' => $userStruct->class(),
]);

/** @var TypePhotoStruct */
foreach ($userStruct->photos() as $idx => $photo) {
    var_dump($idx, $photo->name());
    var_dump($idx, $photo->url());

    // expect that this will throw an error, unless it is part of the loaded struct
    // var_dump($idx, $photo->whatever());
}
