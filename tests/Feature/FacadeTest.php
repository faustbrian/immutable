<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Immutable\Facades\Mutator as MutatorFacade;
use Cline\Immutable\Mutator;
use Tests\Fixtures\UserData;

describe('Mutator Class', function (): void {
    test('mutate() works with arrays', function (): void {
        $result = Mutator::mutate(['name' => 'John'], ['name' => 'Jane']);

        expect($result)->toBe(['name' => 'Jane']);
    });

    test('mutate() works with objects', function (): void {
        $user = new UserData('John', 'john@example.com', 30);
        $updated = Mutator::mutate($user, ['age' => 31]);

        expect($updated)->toBeInstanceOf(UserData::class);
        expect($updated->age)->toBe(31);
    });

    test('for() method works with fluent API', function (): void {
        $result = Mutator::for(['a' => 1, 'b' => 2])
            ->with(['b' => 20])
            ->get();

        expect($result)->toBe(['a' => 1, 'b' => 20]);
    });
});

describe('Mutator Facade', function (): void {
    test('facade accessor returns Mutator class', function (): void {
        $reflection = new ReflectionClass(MutatorFacade::class);
        $method = $reflection->getMethod('getFacadeAccessor');

        $accessor = $method->invoke(null);

        expect($accessor)->toBe(Mutator::class);
    });
});
