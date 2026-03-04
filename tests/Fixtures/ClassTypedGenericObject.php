<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use DateTimeImmutable;

/**
 * Test fixture with class-typed property for validating instanceof checks.
 *
 * @author Brian Faust <brian@cline.sh>
 * @internal
 * @psalm-immutable
 */
final readonly class ClassTypedGenericObject
{
    public function __construct(
        public string $name,
        public DateTimeImmutable $createdAt,
    ) {}
}
