<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Immutable\Exceptions\ImmutableException;
use Cline\Immutable\Exceptions\InvalidTypeException;
use Cline\Immutable\Exceptions\PropertyDoesNotExistException;
use Cline\Immutable\Exceptions\ReadOnlyPropertyException;
use Cline\Immutable\Exceptions\UnsupportedTypeException;

describe('Exceptions', function (): void {
    describe('ImmutableException', function (): void {
        test('is abstract', function (): void {
            $reflection = new ReflectionClass(ImmutableException::class);

            expect($reflection->isAbstract())->toBeTrue();
        });

        test('extends RuntimeException', function (): void {
            expect(is_subclass_of(ImmutableException::class, RuntimeException::class))->toBeTrue();
        });
    });

    describe('PropertyDoesNotExistException', function (): void {
        test('extends ImmutableException', function (): void {
            $exception = PropertyDoesNotExistException::forProperty('prop', 'Class');

            expect($exception)->toBeInstanceOf(ImmutableException::class);
        });

        test('forProperty creates exception with proper message', function (): void {
            $exception = PropertyDoesNotExistException::forProperty('name', 'App\User');

            expect($exception->getMessage())->toBe(
                'Property "name" does not exist on class "App\User".',
            );
        });
    });

    describe('ReadOnlyPropertyException', function (): void {
        test('extends ImmutableException', function (): void {
            $exception = ReadOnlyPropertyException::forProperty('id', 'App\User');

            expect($exception)->toBeInstanceOf(ImmutableException::class);
        });

        test('forProperty creates exception with proper message', function (): void {
            $exception = ReadOnlyPropertyException::forProperty('id', 'App\User');

            expect($exception->getMessage())->toBe(
                'Property "id" on class "App\User" is read-only and cannot be mutated.',
            );
        });
    });

    describe('InvalidTypeException', function (): void {
        test('extends ImmutableException', function (): void {
            $exception = InvalidTypeException::mismatch('prop', 'int', 'string');

            expect($exception)->toBeInstanceOf(ImmutableException::class);
        });

        test('mismatch creates exception with proper message', function (): void {
            $exception = InvalidTypeException::mismatch('age', 'int', 'hello');

            expect($exception->getMessage())->toBe(
                'Property "age" expects type "int", got "string".',
            );
        });

        test('mismatch handles object types', function (): void {
            $exception = InvalidTypeException::mismatch('user', 'App\User', new stdClass());

            expect($exception->getMessage())->toBe(
                'Property "user" expects type "App\User", got "stdClass".',
            );
        });

        test('mismatch handles null', function (): void {
            $exception = InvalidTypeException::mismatch('name', 'string', null);

            expect($exception->getMessage())->toBe(
                'Property "name" expects type "string", got "null".',
            );
        });

        test('mismatch handles arrays', function (): void {
            $exception = InvalidTypeException::mismatch('name', 'string', [1, 2, 3]);

            expect($exception->getMessage())->toBe(
                'Property "name" expects type "string", got "array".',
            );
        });
    });

    describe('UnsupportedTypeException', function (): void {
        test('extends ImmutableException', function (): void {
            $exception = UnsupportedTypeException::forValue('string');

            expect($exception)->toBeInstanceOf(ImmutableException::class);
        });

        test('forValue creates exception with proper message for string', function (): void {
            $exception = UnsupportedTypeException::forValue('hello');

            expect($exception->getMessage())->toBe(
                'Cannot mutate value of type "string". Supported types: object, array.',
            );
        });

        test('forValue creates exception with proper message for int', function (): void {
            $exception = UnsupportedTypeException::forValue(42);

            expect($exception->getMessage())->toBe(
                'Cannot mutate value of type "int". Supported types: object, array.',
            );
        });

        test('forValue creates exception with proper message for null', function (): void {
            $exception = UnsupportedTypeException::forValue(null);

            expect($exception->getMessage())->toBe(
                'Cannot mutate value of type "null". Supported types: object, array.',
            );
        });
    });
});
