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
 * Thrown when the value type doesn't match the property type.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidTypeException extends ImmutableException
{
    /**
     * Create exception for a type mismatch.
     *
     * @param string $property     The property name
     * @param string $expectedType The expected type
     * @param mixed  $actualValue  The actual value provided
     */
    public static function mismatch(string $property, string $expectedType, mixed $actualValue): self
    {
        return new self(sprintf(
            'Property "%s" expects type "%s", got "%s".',
            $property,
            $expectedType,
            get_debug_type($actualValue),
        ));
    }
}
