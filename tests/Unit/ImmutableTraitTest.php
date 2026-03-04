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
use Tests\Fixtures\AllTypesObject;
use Tests\Fixtures\OperationData;
use Tests\Fixtures\OperationStatus;
use Tests\Fixtures\UnionTypeObject;
use Tests\Fixtures\UntypedMutableObject;
use Tests\Fixtures\UserData;

describe('Immutable Trait', function (): void {
    describe('basic mutation', function (): void {
        test('creates a new instance with modified properties', function (): void {
            $user = new UserData('John', 'john@example.com', 30);
            $updated = $user->mutate(['age' => 31]);

            expect($updated)->toBeInstanceOf(UserData::class);
            expect($updated)->not->toBe($user);
            expect($updated->age)->toBe(31);
            expect($updated->name)->toBe('John');
            expect($updated->email)->toBe('john@example.com');
        });

        test('original instance remains unchanged', function (): void {
            $user = new UserData('John', 'john@example.com', 30);
            $user->mutate(['age' => 31]);

            expect($user->age)->toBe(30);
        });

        test('can modify multiple properties at once', function (): void {
            $user = new UserData('John', 'john@example.com', 30);
            $updated = $user->mutate([
                'name' => 'Jane',
                'email' => 'jane@example.com',
            ]);

            expect($updated->name)->toBe('Jane');
            expect($updated->email)->toBe('jane@example.com');
            expect($updated->age)->toBe(30);
        });

        test('can mutate all properties', function (): void {
            $user = new UserData('John', 'john@example.com', 30);
            $updated = $user->mutate([
                'name' => 'Jane',
                'email' => 'jane@example.com',
                'age' => 25,
            ]);

            expect($updated->name)->toBe('Jane');
            expect($updated->email)->toBe('jane@example.com');
            expect($updated->age)->toBe(25);
        });

        test('returns same type when mutating', function (): void {
            $user = new UserData('John', 'john@example.com', 30);
            $updated = $user->mutate(['age' => 31]);

            expect($updated)->toBeInstanceOf(UserData::class);
        });
    });

    describe('complex objects', function (): void {
        test('can mutate objects with enum properties', function (): void {
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

            $cancelled = $operation->mutate([
                'status' => OperationStatus::Cancelled,
                'cancelledAt' => $now,
            ]);

            expect($cancelled->status)->toBe(OperationStatus::Cancelled);
            expect($cancelled->cancelledAt)->toBe($now);
            expect($cancelled->id)->toBe('op-123');
            expect($cancelled->progress)->toBe(50);
        });

        test('preserves nullable properties when not mutated', function (): void {
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
                metadata: [],
            );

            $updated = $operation->mutate(['progress' => 75]);

            expect($updated->completedAt)->toBeNull();
            expect($updated->cancelledAt)->toBeNull();
            expect($updated->startedAt)->toBe($now);
        });

        test('can set nullable properties to a value', function (): void {
            $now = CarbonImmutable::now();
            $operation = new OperationData(
                id: 'op-123',
                function: 'process',
                version: '1.0.0',
                status: OperationStatus::Running,
                progress: 100,
                result: null,
                errors: [],
                startedAt: $now,
                completedAt: null,
                cancelledAt: null,
                metadata: [],
            );

            $completed = $operation->mutate([
                'status' => OperationStatus::Completed,
                'completedAt' => $now,
                'result' => ['success' => true],
            ]);

            expect($completed->completedAt)->toBe($now);
            expect($completed->result)->toBe(['success' => true]);
        });
    });

    describe('error handling', function (): void {
        test('throws PropertyDoesNotExistException for non-existent property', function (): void {
            $user = new UserData('John', 'john@example.com', 30);

            expect(fn (): UserData => $user->mutate(['nonexistent' => 'value']))
                ->toThrow(PropertyDoesNotExistException::class);
        });

        test('throws InvalidTypeException for type mismatch', function (): void {
            $user = new UserData('John', 'john@example.com', 30);

            expect(fn (): UserData => $user->mutate(['age' => 'not an integer']))
                ->toThrow(InvalidTypeException::class);
        });

        test('throws InvalidTypeException for wrong object type', function (): void {
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
                metadata: [],
            );

            expect(fn (): OperationData => $operation->mutate(['status' => 'invalid']))
                ->toThrow(InvalidTypeException::class);
        });
    });

    describe('edge cases', function (): void {
        test('can mutate with empty array (creates clone)', function (): void {
            $user = new UserData('John', 'john@example.com', 30);
            $clone = $user->mutate([]);

            expect($clone)->not->toBe($user);
            expect($clone->name)->toBe('John');
            expect($clone->email)->toBe('john@example.com');
            expect($clone->age)->toBe(30);
        });
    });

    describe('type validation', function (): void {
        test('ignores static properties during mutation', function (): void {
            $obj = new AllTypesObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            AllTypesObject::$staticProp = 'modified';
            $mutated = $obj->mutate(['intProp' => 2]);

            expect($mutated->intProp)->toBe(2);
            expect(AllTypesObject::$staticProp)->toBe('modified');
        });

        test('validates float type accepts integers', function (): void {
            $obj = new AllTypesObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            $mutated = $obj->mutate(['floatProp' => 2]);

            expect($mutated->floatProp)->toBe(2.0);
        });

        test('validates bool type', function (): void {
            $obj = new AllTypesObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            $mutated = $obj->mutate(['boolProp' => false]);

            expect($mutated->boolProp)->toBe(false);
        });

        test('validates array type', function (): void {
            $obj = new AllTypesObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            $mutated = $obj->mutate(['arrayProp' => ['b', 'c']]);

            expect($mutated->arrayProp)->toBe(['b', 'c']);
        });

        test('validates object type', function (): void {
            $newObj = new stdClass();
            $newObj->foo = 'bar';

            $obj = new AllTypesObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            $mutated = $obj->mutate(['objectProp' => $newObj]);

            expect($mutated->objectProp)->toBe($newObj);
        });

        test('validates mixed type accepts anything', function (): void {
            $obj = new AllTypesObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
            );

            $mutated = $obj->mutate(['mixedProp' => 123]);

            expect($mutated->mixedProp)->toBe(123);
        });

        test('allows null for nullable properties', function (): void {
            $obj = new AllTypesObject(
                intProp: 1,
                floatProp: 1.5,
                stringProp: 'test',
                boolProp: true,
                arrayProp: ['a'],
                objectProp: new stdClass(),
                mixedProp: 'anything',
                nullableProp: 'value',
            );

            $mutated = $obj->mutate(['nullableProp' => null]);

            expect($mutated->nullableProp)->toBeNull();
        });

        test('skips validation for union types', function (): void {
            $obj = new UnionTypeObject(value: 'string');

            $mutated = $obj->mutate(['value' => 123]);

            expect($mutated->value)->toBe(123);
        });

        test('skips validation for untyped properties', function (): void {
            $obj = new UntypedMutableObject(name: 'test', value: 42);

            $mutated = $obj->mutate(['value' => 'now a string']);

            expect($mutated->value)->toBe('now a string');
        });
    });
});
