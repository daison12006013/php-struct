<?php

define('STRUCT_PARAM_CHECKING', true);
error_reporting(E_ALL & ~E_DEPRECATED);

use Daison\Struct\Collection;
use Daison\Struct\Contract;
use Daison\Struct\Struct;
use Daison\Struct\TypeException;

/**
 * @method string                email()     Get the email
 * @method string                firstName() Get the first name
 * @method string                lastName()  Get the last name
 * @method string                gender()    Get the gender
 * @method int                   age()       Get the age
 * @method Collection            photos()    Get the lists of photos
 * @method bool                  married()
 * @method TypeLocationInterface location()
 * @method Closure               closure()
 * @method DummyClass            class()
 */
interface TypeUserInterface extends Contract
{
}

/**
 * @method string url()
 * @method string name()
 */
interface TypePhotoInterface extends Contract
{
}

/**
 * @method float x()
 * @method float y()
 */
interface TypeLocationInterface extends Contract
{
}

$locationStruct = new Struct([
    'x' => fn (float $x): float => $x,
    'y' => fn (float $y): float => $y,
]);

/** @var TypePhotoInterface */
$photoStruct = new Struct([
    'name' => fn ($name): string => $name,
    'url' => fn ($url): string => $url,
]);

/** @var TypeUserInterface */
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

test('value returned', function () use ($userStruct) {
    expect($userStruct->email())->toBe('johndoe@email.com');
    expect($userStruct->firstName())->toBe('John');
    expect($userStruct->lastName())->toBe('Doe');
    expect($userStruct->gender())->toBe('male');
    expect($userStruct->age())->toBe(31);
    expect($userStruct->married())->toBe(true);
});

test('returned types', function () use ($userStruct) {
    expect($userStruct->email())->toBeString();
    expect($userStruct->firstName())->toBeString();
    expect($userStruct->lastName())->toBeString();
    expect($userStruct->gender())->toBeString();
    expect($userStruct->age())->toBeInt();
    expect($userStruct->married())->toBeBool();
    expect($userStruct->toArray())->toBeArray();
    expect($userStruct->closure())->toBeCallable();
    expect($userStruct->class())->toBeInstanceOf(DummyClass::class);

    expect($userStruct->location())->toBeInstanceOf(Struct::class);
    expect($userStruct->location()->x())->toBeFloat();
    expect($userStruct->location()->y())->toBeFloat();

    expect($userStruct->photos())->toBeInstanceOf(Collection::class);
    expect($userStruct->photos()[0])->toBeInstanceOf(Struct::class);
    expect($userStruct->photos()[0]->name())->toBeString();
});

test('collection', function () use ($userStruct) {
    expect($userStruct->photos())->toBeInstanceOf(Collection::class);
    expect($userStruct->photos()->empty())->toBe(false);

    /** @var TypePhotoInterface */
    foreach ($userStruct->photos() as $photo) {
        expect($photo->name())->toBe('GitHub');
        expect($photo->url())->toBe('https://github.com/daison12006013');

        // expect that this will throw an error, unless it is part of the loaded struct
        expect(fn () => $photo->whatever())->toThrow(
            TypeException::class,
            'Struct: Undefined struct data [whatever]'
        );
    }
});

test('return type expects [int] but value is [string]', function () {
    /** @var TypeUserInterface */
    $struct = new Struct([
        'email' => fn (string $email): int => $email,
    ]);

    $struct->load(['email' => 'johndoe@email.com']);

    expect(fn () => $struct->email())->toThrow(
        TypeException::class,
        'Struct: Return type of [email] expects [int] but value is johndoe@email.com typed [string]'
    );
});

test('data type expects [int] but value is [string]', function () {
    /** @var TypeUserInterface */
    $struct = new Struct([
        'email' => fn (int $email): string => $email,
    ]);

    $struct->load(['email' => 'johndoe@email.com']);

    expect(fn () => $struct->email())->toThrow(
        TypeException::class,
        'Struct: Data type of [email] expects [int] but value is johndoe@email.com typed [string]'
    );
});

test('loaded with empty data', function () {
    $struct = new Struct([
        'email' => fn (string $email): string => $email,
    ]);

    expect(fn () => $struct->eeeeeemail())->toThrow(
        TypeException::class,
        'Struct: Undefined struct data [eeeeeemail]'
    );

    expect(fn () => $struct->email())->toThrow(
        \Whoops\Exception\ErrorException::class,
        'Trying to access array offset on value of type null'
    );

    // load the email
    $struct->load(['email' => 'johndoe@email.com']);
    expect($struct->email())->toBe('johndoe@email.com');
});

test('optional keys should still be generated', function () {
    $struct = new Struct([
        'age' => fn (int $age) => $age,
        'email' => fn (string $email): string => $email,
    ]);

    $struct->load([
        'email' => 'johndoe@email.com'
    ]);

    expect($struct->toArray())->toBe([
        'age'   => null,
        'email' => 'johndoe@email.com',
    ]);
    expect($struct->age())->toBe(null);
    expect($struct->email())->toBe('johndoe@email.com');
});
