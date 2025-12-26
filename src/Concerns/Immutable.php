<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Immutable\Concerns;

use Cline\Immutable\Exceptions\InvalidTypeException;
use Cline\Immutable\Exceptions\PropertyDoesNotExistException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

use function array_key_exists;
use function array_keys;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function property_exists;

/**
 * Enables immutable mutation for objects.
 *
 * This trait provides a `mutate()` method that creates a new instance
 * of the object with specified properties modified, while leaving the
 * original instance unchanged.
 *
 * ```php
 * class UserData
 * {
 *     use Immutable;
 *
 *     public function __construct(
 *         public readonly string $name,
 *         public readonly string $email,
 *         public readonly int $age,
 *     ) {}
 * }
 *
 * $user = new UserData('John', 'john@example.com', 30);
 * $updated = $user->mutate(['age' => 31]);
 * // $user->age === 30 (unchanged)
 * // $updated->age === 31 (new instance)
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @phpstan-ignore trait.unused
 */
trait Immutable
{
    /**
     * Create a new instance with the specified properties modified.
     *
     * Clones the current instance and applies the provided property changes.
     * Validates that all properties exist and have compatible types before
     * creating the new instance.
     *
     * @param array<string, mixed> $properties Properties to modify in the new instance
     *
     * @throws InvalidPropertyException When a property doesn't exist
     * @throws InvalidTypeException     When a value type doesn't match the property type
     * @return static                   A new instance with the modified properties
     */
    public function mutate(array $properties): static
    {
        $reflection = new ReflectionClass($this);
        $clone = $reflection->newInstanceWithoutConstructor();

        // Copy all current property values to the clone
        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $name = $property->getName();

            if (array_key_exists($name, $properties)) {
                $this->validateProperty($property, $properties[$name]);
                $property->setValue($clone, $properties[$name]);
            } elseif ($property->isInitialized($this)) {
                $property->setValue($clone, $property->getValue($this));
            }
        }

        // Validate that no unknown properties were passed
        foreach (array_keys($properties) as $name) {
            if (!property_exists($this, $name)) {
                throw PropertyDoesNotExistException::forProperty($name, static::class);
            }
        }

        return $clone;
    }

    /**
     * Validate that a value is compatible with a property's type.
     *
     * @param ReflectionProperty $property The property to validate against
     * @param mixed              $value    The value to validate
     *
     * @throws InvalidTypeException When the value type doesn't match
     */
    private function validateProperty(ReflectionProperty $property, mixed $value): void
    {
        $type = $property->getType();

        if ($type === null) {
            return; // No type constraint
        }

        if (!$type instanceof ReflectionNamedType) {
            return; // Union/intersection types - skip validation
        }

        if ($type->allowsNull() && $value === null) {
            return;
        }

        $typeName = $type->getName();
        $isValid = match ($typeName) {
            'int' => is_int($value),
            'float' => is_float($value) || is_int($value),
            'string' => is_string($value),
            'bool' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'mixed' => true,
            default => $value instanceof $typeName,
        };

        if (!$isValid) {
            throw InvalidTypeException::mismatch(
                $property->getName(),
                $typeName,
                $value,
            );
        }
    }
}
