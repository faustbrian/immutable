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
 * Test fixture with all scalar types for coverage.
 *
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final class AllTypesObject implements Mutable
{
    use Immutable;

    public static string $staticProp = 'static';

    public function __construct(
        public readonly int $intProp,
        public readonly float $floatProp,
        public readonly string $stringProp,
        public readonly bool $boolProp,
        public readonly array $arrayProp,
        public readonly object $objectProp,
        public readonly mixed $mixedProp,
        public readonly ?string $nullableProp = null,
    ) {}
}
