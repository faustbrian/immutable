<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Immutable\Exceptions\InvalidTypeException;
use Cline\Immutable\Exceptions\PropertyDoesNotExistException;
use Cline\Immutable\Mutator;
use Tests\Fixtures\AllTypesGenericObject;
use Tests\Fixtures\ClassTypedGenericObject;
use Tests\Fixtures\GenericObject;
use Tests\Fixtures\ImmutableWithoutContract;
use Tests\Fixtures\OperationData;
use Tests\Fixtures\OperationStatus;
use Tests\Fixtures\UnionTypeGenericObject;
use Tests\Fixtures\UntypedObject;
use Tests\Fixtures\UserData;

describe('Mutator', function (): void {
    describe('static mutate method', function (): void {
        test('mutates arrays', function (): void {
            $original = ['name' => 'John', 'age' => 30];
            $updated = Mutator::mutate($original, ['age' => 31]);

            expect($updated)->toBe(['name' => 'John', 'age' => 31]);
            expect($original)->toBe(['name' => 'John', 'age' => 30]);
        });

        test('mutates objects with Immutable trait', function (): void {
            $user = new UserData('John', 'john@example.com', 30);
            $updated = Mutator::mutate($user, ['age' => 31]);

            expect($updated)->toBeInstanceOf(UserData::class);
            expect($updated->age)->toBe(31);
            expect($user->age)->toBe(30);
        });

        test('mutates generic objects', function (): void {
            $obj = new GenericObject('test', 42);
            $updated = Mutator::mutate($obj, ['value' => 100]);

            expect($updated)->toBeInstanceOf(GenericObject::class);
            expect($updated->value)->toBe(100);
            expect($obj->value)->toBe(42);
        });

        test('mutates stdClass objects', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';
            $obj->age = 30;

            $updated = Mutator::mutate($obj, ['age' => 31]);

            expect($updated)->toBeInstanceOf(stdClass::class);
            expect($updated->age)->toBe(31);
            expect($obj->age)->toBe(30);
        });

        test('adds new properties to stdClass', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';

            $updated = Mutator::mutate($obj, ['email' => 'john@example.com']);

            expect($updated->name)->toBe('John');
            expect($updated->email)->toBe('john@example.com');
        });
    });

    describe('fluent API', function (): void {
        test('for() starts a fluent chain', function (): void {
            $mutator = Mutator::for(['name' => 'John']);

            expect($mutator)->toBeInstanceOf(Mutator::class);
        });

        test('with() adds properties to mutate', function (): void {
            $result = Mutator::for(['name' => 'John', 'age' => 30])
                ->with(['age' => 31])
                ->get();

            expect($result)->toBe(['name' => 'John', 'age' => 31]);
        });

        test('set() adds a single property', function (): void {
            $result = Mutator::for(['name' => 'John', 'age' => 30])
                ->set('age', 31)
                ->get();

            expect($result)->toBe(['name' => 'John', 'age' => 31]);
        });

        test('can chain multiple with() calls', function (): void {
            $result = Mutator::for(['a' => 1, 'b' => 2, 'c' => 3])
                ->with(['a' => 10])
                ->with(['b' => 20])
                ->get();

            expect($result)->toBe(['a' => 10, 'b' => 20, 'c' => 3]);
        });

        test('can chain set() and with()', function (): void {
            $result = Mutator::for(['a' => 1, 'b' => 2, 'c' => 3])
                ->set('a', 10)
                ->with(['b' => 20, 'c' => 30])
                ->get();

            expect($result)->toBe(['a' => 10, 'b' => 20, 'c' => 30]);
        });

        test('later mutations override earlier ones', function (): void {
            $result = Mutator::for(['name' => 'John'])
                ->set('name', 'Jane')
                ->set('name', 'Bob')
                ->get();

            expect($result)->toBe(['name' => 'Bob']);
        });
    });

    describe('array mutation', function (): void {
        test('merges arrays correctly', function (): void {
            $original = ['a' => 1, 'b' => 2];
            $result = Mutator::mutate($original, ['c' => 3]);

            expect($result)->toBe(['a' => 1, 'b' => 2, 'c' => 3]);
        });

        test('overwrites existing keys', function (): void {
            $original = ['a' => 1, 'b' => 2];
            $result = Mutator::mutate($original, ['a' => 10]);

            expect($result)->toBe(['a' => 10, 'b' => 2]);
        });

        test('handles nested arrays', function (): void {
            $original = ['user' => ['name' => 'John', 'age' => 30]];
            $result = Mutator::mutate($original, ['user' => ['name' => 'Jane', 'age' => 25]]);

            expect($result['user'])->toBe(['name' => 'Jane', 'age' => 25]);
        });

        test('handles empty arrays', function (): void {
            $result = Mutator::mutate([], ['a' => 1]);

            expect($result)->toBe(['a' => 1]);
        });

        test('handles numeric arrays', function (): void {
            $original = [1, 2, 3];
            $result = Mutator::mutate($original, [0 => 10]);

            expect($result)->toBe([10, 2, 3]);
        });
    });

    describe('object mutation', function (): void {
        test('preserves unmodified properties', function (): void {
            $obj = new GenericObject('test', 42, 'optional');
            $updated = Mutator::mutate($obj, ['name' => 'changed']);

            expect($updated->name)->toBe('changed');
            expect($updated->value)->toBe(42);
            expect($updated->optional)->toBe('optional');
        });

        test('handles nullable properties', function (): void {
            $obj = new GenericObject('test', 42);
            $updated = Mutator::mutate($obj, ['optional' => 'now set']);

            expect($updated->optional)->toBe('now set');
        });

        test('can set nullable properties back to null', function (): void {
            $obj = new GenericObject('test', 42, 'value');
            $updated = Mutator::mutate($obj, ['optional' => null]);

            expect($updated->optional)->toBeNull();
        });

        test('handles objects with untyped properties', function (): void {
            $obj = new UntypedObject('test', 42);
            $updated = Mutator::mutate($obj, ['value' => 'string now']);

            expect($updated->value)->toBe('string now');
        });
    });

    describe('complex scenarios', function (): void {
        test('mutates operation data like the user example', function (): void {
            $now = CarbonImmutable::now();
            $operation = new OperationData(
                id: 'op-123',
                function: 'process',
                version: '1.0.0',
                status: OperationStatus::Running,
                progress: 50,
                result: null,
                errors: [],
                startedAt: $now,
                completedAt: null,
                cancelledAt: null,
                metadata: ['key' => 'value'],
            );

            // Instead of creating a whole new object manually:
            // $cancelledOperation = new OperationData(
            //     id: $operation->id,
            //     function: $operation->function,
            //     ...
            // );

            // Just mutate the properties that changed:
            $cancelled = Mutator::mutate($operation, [
                'status' => OperationStatus::Cancelled,
                'cancelledAt' => $now,
            ]);

            expect($cancelled->status)->toBe(OperationStatus::Cancelled);
            expect($cancelled->cancelledAt)->toBe($now);
            expect($cancelled->id)->toBe('op-123');
            expect($cancelled->function)->toBe('process');
            expect($cancelled->version)->toBe('1.0.0');
            expect($cancelled->progress)->toBe(50);
            expect($cancelled->metadata)->toBe(['key' => 'value']);
        });
    });

    describe('error handling', function (): void {
        test('throws PropertyDoesNotExistException for non-existent property on generic object', function (): void {
            $obj = new GenericObject('test', 42);

            expect(fn (): object|array => Mutator::mutate($obj, ['nonexistent' => 'value']))
                ->toThrow(PropertyDoesNotExistException::class);
        });

        test('throws InvalidTypeException for type mismatch on generic object', function (): void {
            $obj = new GenericObject('test', 42);

            expect(fn (): object|array => Mutator::mutate($obj, ['value' => 'not an int']))
                ->toThrow(InvalidTypeException::class);
        });
    });

    describe('generic object type validation', function (): void {
        test('ignores static properties during mutation', function (): void {
            $obj = new AllTypesGenericObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            AllTypesGenericObject::$staticProp = 'modified';
            $mutated = Mutator::mutate($obj, ['intProp' => 2]);

            expect($mutated->intProp)->toBe(2);
            expect(AllTypesGenericObject::$staticProp)->toBe('modified');
        });

        test('validates float type accepts integers', function (): void {
            $obj = new AllTypesGenericObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            $mutated = Mutator::mutate($obj, ['floatProp' => 2]);

            expect($mutated->floatProp)->toBe(2.0);
        });

        test('validates bool type', function (): void {
            $obj = new AllTypesGenericObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            $mutated = Mutator::mutate($obj, ['boolProp' => false]);

            expect($mutated->boolProp)->toBe(false);
        });

        test('validates array type', function (): void {
            $obj = new AllTypesGenericObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            $mutated = Mutator::mutate($obj, ['arrayProp' => ['b', 'c']]);

            expect($mutated->arrayProp)->toBe(['b', 'c']);
        });

        test('validates object type', function (): void {
            $newObj = new stdClass();
            $newObj->foo = 'bar';

            $obj = new AllTypesGenericObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            $mutated = Mutator::mutate($obj, ['objectProp' => $newObj]);

            expect($mutated->objectProp)->toBe($newObj);
        });

        test('validates mixed type accepts anything', function (): void {
            $obj = new AllTypesGenericObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            $mutated = Mutator::mutate($obj, ['mixedProp' => 123]);

            expect($mutated->mixedProp)->toBe(123);
        });

        test('allows null for nullable properties', function (): void {
            $obj = new AllTypesGenericObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
                nullableProp: 'value',
            );

            $mutated = Mutator::mutate($obj, ['nullableProp' => null]);

            expect($mutated->nullableProp)->toBeNull();
        });

        test('skips validation for union types', function (): void {
            $obj = new UnionTypeGenericObject(value: 'string');

            $mutated = Mutator::mutate($obj, ['value' => 123]);

            expect($mutated->value)->toBe(123);
        });

        test('validates class-typed properties with instanceof', function (): void {
            $now = CarbonImmutable::now();
            $later = CarbonImmutable::now()->addDays(1);
            $obj = new ClassTypedGenericObject(name: 'test', createdAt: $now);

            $mutated = Mutator::mutate($obj, ['createdAt' => $later]);

            expect($mutated->createdAt)->toBe($later);
        });
    });

    describe('trait detection', function (): void {
        test('mutates objects using Immutable trait without Mutable interface', function (): void {
            $obj = new ImmutableWithoutContract(name: 'test', value: 42);

            $mutated = Mutator::mutate($obj, ['value' => 100]);

            expect($mutated)->toBeInstanceOf(ImmutableWithoutContract::class);
            expect($mutated->value)->toBe(100);
            expect($obj->value)->toBe(42);
        });
    });
});
