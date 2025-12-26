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
use DateTimeImmutable;

/**
 * Test fixture representing an operation data transfer object.
 * Similar to the use case from the user's example.
 *
 * @author Brian Faust <brian@cline.sh>
 * @internal
 * @psalm-immutable
 */
final readonly class OperationData implements Mutable
{
    use Immutable;

    public function __construct(
        public string $id,
        public string $function,
        public string $version,
        public OperationStatus $status,
        public int $progress,
        public mixed $result,
        public array $errors,
        public ?DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $completedAt,
        public ?DateTimeImmutable $cancelledAt,
        public array $metadata,
    ) {}
}
