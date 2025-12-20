<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Immutable;

use Cline\Immutable\Concerns\Immutable;
use Cline\Immutable\Contracts\Mutable;
use Cline\Immutable\Exceptions\InvalidTypeException;
use Cline\Immutable\Exceptions\PropertyDoesNotExistException;
use Cline\Immutable\Exceptions\UnsupportedTypeException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use stdClass;

use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_replace;
use function class_uses_recursive;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function property_exists;

/**
 * Utility class for immutable mutation of any object or array.
 *
 * Provides static methods to create modified copies of objects and arrays
 * without changing the originals. Works with any object type, not just
 * those using the Immutable trait.
 *
 * ```php
 * // Mutate any object
 * $updated = Mutator::mutate($user, ['age' => 31]);
 *
 * // Mutate arrays
 * $updated = Mutator::mutate(['name' => 'John'], ['name' => 'Jane']);
 *
 * // Fluent API
 * $updated = Mutator::for($user)->with(['age' => 31])->get();
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Mutator
{
    /**
     * Accumulated mutations to apply.
     *
     * @var array<string, mixed>
     */
    private array $mutations = [];

    /**
     * Create a new Mutator instance.
     *
     * @param array<string, mixed>|object $value The value to mutate
     */
    private function __construct(
        /**
         * The value being mutated.
         *
         * @var array<string, mixed>|object
         */
        private readonly array|object $value,
    ) {}

    /**
     * Create a mutated copy of a value with the specified changes.
     *
     * @param array<string, mixed>|object $value      The value to mutate
     * @param array<string, mixed>        $properties The properties/keys to modify
     *
     * @throws InvalidTypeException          When a value type doesn't match the property type
     * @throws PropertyDoesNotExistException When a property doesn't exist on an object
     * @throws UnsupportedTypeException      When the value type is not supported
     * @return array<string, mixed>|object   A new copy with the modifications
     */
    public static function mutate(array|object $value, array $properties): array|object
    {
        return self::for($value)->with($properties)->get();
    }

    /**
     * Start a fluent mutation chain for a value.
     *
     * @param  array<string, mixed>|object $value The value to mutate
     * @return self                        A new Mutator instance for fluent chaining
     */
    public static function for(array|object $value): self
    {
        return new self($value);
    }

    /**
     * Add properties to be modified in the mutation.
     *
     * @param  array<string, mixed> $properties The properties/keys to modify
     * @return $this                For method chaining
     */
    public function with(array $properties): self
    {
        $this->mutations = array_merge($this->mutations, $properties);

        return $this;
    }

    /**
     * Set a single property to be modified.
     *
     * @param  string $property The property/key name
     * @param  mixed  $value    The new value
     * @return $this  For method chaining
     */
    public function set(string $property, mixed $value): self
    {
        $this->mutations[$property] = $value;

        return $this;
    }

    /**
     * Execute the mutation and return the new value.
     *
     * @throws InvalidTypeException          When a value type doesn't match the property type
     * @throws PropertyDoesNotExistException When a property doesn't exist on an object
     * @return array<string, mixed>|object   A new copy with all accumulated modifications
     */
    public function get(): array|object
    {
        if (is_array($this->value)) {
            return $this->mutateArray($this->value, $this->mutations);
        }

        return $this->mutateObject($this->value, $this->mutations);
    }

    /**
     * Mutate an array by replacing/adding values.
     *
     * Uses array_replace which preserves numeric keys and overwrites
     * existing values while adding new keys.
     *
     * @param  array<string, mixed> $array      The original array
     * @param  array<string, mixed> $properties The properties to modify
     * @return array<string, mixed> A new array with the modifications
     */
    private function mutateArray(array $array, array $properties): array
    {
        return array_replace($array, $properties);
    }

    /**
     * Mutate an object by creating a modified clone.
     *
     * If the object uses the Immutable trait or implements Mutable,
     * delegates to the object's own mutate method. Otherwise,
     * creates a clone and sets properties directly.
     *
     * @param object               $object     The original object
     * @param array<string, mixed> $properties The properties to modify
     *
     * @throws InvalidTypeException          When a value type doesn't match
     * @throws PropertyDoesNotExistException When a property doesn't exist
     * @return object                        A new object with the modifications
     */
    private function mutateObject(object $object, array $properties): object
    {
        // Use the object's own mutate method if available
        if ($object instanceof Mutable) {
            return $object->mutate($properties);
        }

        // Check if the object uses the Immutable trait
        $traits = class_uses_recursive($object);

        if (isset($traits[Immutable::class])) {
            /** @var Mutable&object $object */
            return $object->mutate($properties);
        }

        // Handle stdClass objects
        if ($object instanceof stdClass) {
            $clone = clone $object;

            foreach ($properties as $name => $value) {
                $clone->{$name} = $value;
            }

            return $clone;
        }

        // Generic object mutation using reflection
        return $this->mutateGenericObject($object, $properties);
    }

    /**
     * Mutate a generic object using reflection.
     *
     * @param object               $object     The original object
     * @param array<string, mixed> $properties The properties to modify
     *
     * @throws InvalidTypeException          When a value type doesn't match
     * @throws PropertyDoesNotExistException When a property doesn't exist
     * @return object                        A new object with the modifications
     */
    private function mutateGenericObject(object $object, array $properties): object
    {
        $reflection = new ReflectionClass($object);
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
            } elseif ($property->isInitialized($object)) {
                $property->setValue($clone, $property->getValue($object));
            }
        }

        // Validate that no unknown properties were passed
        foreach (array_keys($properties) as $name) {
            if (!property_exists($object, $name)) {
                throw PropertyDoesNotExistException::forProperty($name, $object::class);
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
