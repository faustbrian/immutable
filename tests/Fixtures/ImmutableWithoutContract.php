<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Immutable\Concerns\Immutable;

/**
 * Test fixture using Immutable trait but NOT implementing Mutable interface.
 * This tests the class_uses_recursive path in Mutator.
 *
 * @author Brian Faust <brian@cline.sh>
 * @internal
 * @psalm-immutable
 */
final readonly class ImmutableWithoutContract
{
    use Immutable;

    public function __construct(
        public string $name,
        public int $value,
    ) {}
}
