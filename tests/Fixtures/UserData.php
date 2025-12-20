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
 * Test fixture representing a user data transfer object.
 *
 * @author Brian Faust <brian@cline.sh>
 * @internal
 * @psalm-immutable
 */
final readonly class UserData implements Mutable
{
    use Immutable;

    public function __construct(
        public string $name,
        public string $email,
        public int $age,
    ) {}
}
