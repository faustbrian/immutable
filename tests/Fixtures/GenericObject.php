<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

/**
 * Test fixture for a generic object without the Immutable trait.
 *
 * @author Brian Faust <brian@cline.sh>
 * @internal
 * @psalm-immutable
 */
final readonly class GenericObject
{
    public function __construct(
        public string $name,
        public int $value,
        public ?string $optional = null,
    ) {}
}
