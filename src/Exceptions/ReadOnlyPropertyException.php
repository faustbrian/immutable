<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Immutable\Exceptions;

use function sprintf;

/**
 * Thrown when attempting to mutate a read-only property.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ReadOnlyPropertyException extends ImmutableException
{
    /**
     * Create exception for a read-only property that cannot be mutated.
     *
     * @param string       $property The property name that is read-only
     * @param class-string $class    The class being mutated
     */
    public static function forProperty(string $property, string $class): self
    {
        return new self(sprintf(
            'Property "%s" on class "%s" is read-only and cannot be mutated.',
            $property,
            $class,
        ));
    }
}
