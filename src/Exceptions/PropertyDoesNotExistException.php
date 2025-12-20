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
 * Thrown when attempting to mutate a property that doesn't exist.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class PropertyDoesNotExistException extends ImmutableException
{
    /**
     * Create exception for a non-existent property.
     *
     * @param string       $property The property name that doesn't exist
     * @param class-string $class    The class being mutated
     */
    public static function forProperty(string $property, string $class): self
    {
        return new self(sprintf(
            'Property "%s" does not exist on class "%s".',
            $property,
            $class,
        ));
    }
}
