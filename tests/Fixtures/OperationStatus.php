<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

/**
 * Test fixture enum representing operation status.
 *
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
enum OperationStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
}
