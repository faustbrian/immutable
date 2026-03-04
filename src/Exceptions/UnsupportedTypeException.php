<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Immutable\Exceptions;

use function get_debug_type;
use function sprintf;

/**
 * Thrown when attempting to mutate an unsupported value type.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class UnsupportedTypeException extends ImmutableException
{
    /**
     * Create exception for an unsupported type.
     *
     * @param mixed $value The unsupported value
     */
    public static function forValue(mixed $value): self
    {
        return new self(sprintf(
            'Cannot mutate value of type "%s". Supported types: object, array.',
            get_debug_type($value),
        ));
    }
}
