<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Immutable\Concerns\Immutable;
use Cline\Immutable\Contracts\Mutable;

/**
 * Test fixture with union type properties for coverage.
 *
 * @author Brian Faust <brian@cline.sh>
 * @internal
 * @psalm-immutable
 */
final readonly class UnionTypeObject implements Mutable
{
    use Immutable;

    public function __construct(
        public int|string $value,
    ) {}
}
