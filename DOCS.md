## Table of Contents

1. [Overview](#doc-docs-readme) (`docs/README.md`)
2. [Exceptions](#doc-docs-exceptions) (`docs/exceptions.md`)
3. [Static Mutations](#doc-docs-static-mutations) (`docs/static-mutations.md`)
4. [Trait Usage](#doc-docs-trait-usage) (`docs/trait-usage.md`)
<a id="doc-docs-readme"></a>

## Installation

Install via Composer:

```bash
composer require cline/immutable
```

The service provider will be automatically registered via Laravel's package discovery.

## What is Immutable?

Immutable provides utilities for working with immutable objects and arrays in PHP. Instead of modifying objects directly, you create new copies with your desired changes - the original remains untouched.

### Why Immutability?

- **Predictable State** - Objects don't change unexpectedly
- **Thread Safety** - No race conditions from shared mutable state
- **Debugging** - Trace exactly when and where state changes occur
- **Functional Patterns** - Enable pure functions that don't mutate inputs

## Quick Start

### Using the Immutable Trait

Add the `Immutable` trait to your data objects:

```php
<?php

use Cline\Immutable\Concerns\Immutable;
use Cline\Immutable\Contracts\Mutable;

final class UserData implements Mutable
{
    use Immutable;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly int $age,
    ) {}
}

$user = new UserData('John', 'john@example.com', 30);

// Create a new instance with modified age
$updated = $user->mutate(['age' => 31]);

$user->age;     // 30 (unchanged)
$updated->age;  // 31 (new instance)
```

### Using the Mutator Class

The `Mutator` class works with any object or array:

```php
<?php

use Cline\Immutable\Mutator;

// Mutate arrays
$original = ['name' => 'John', 'age' => 30];
$updated = Mutator::mutate($original, ['age' => 31]);

// Mutate any object
$user = new UserData('John', 'john@example.com', 30);
$updated = Mutator::mutate($user, ['name' => 'Jane']);

// Fluent API
$updated = Mutator::for($user)
    ->with(['name' => 'Jane'])
    ->set('age', 25)
    ->get();
```

## Two Approaches

Immutable offers two complementary approaches:

### 1. Trait-Based (Recommended for Your Classes)

Use the `Immutable` trait when you control the class definition. This provides a clean `mutate()` method directly on your objects.

```php
$updated = $user->mutate(['age' => 31]);
```

### 2. Static Mutator (Works with Any Object)

Use the `Mutator` class when working with third-party objects, stdClass, or arrays.

```php
$updated = Mutator::mutate($anyObject, ['property' => 'value']);
```

## Type Safety

Both approaches validate types at runtime:

```php
// Throws InvalidTypeException - age must be int
$user->mutate(['age' => 'thirty']);

// Throws PropertyDoesNotExistException - property doesn't exist
$user->mutate(['nonexistent' => 'value']);
```

## Next Steps

- **[Trait Usage](trait-usage)** - Detailed guide to using the Immutable trait
- **[Static Mutations](static-mutations)** - Using the Mutator class for arrays and objects
- **[Exceptions](exceptions)** - Understanding error handling

<a id="doc-docs-exceptions"></a>

## Exception Hierarchy

All exceptions extend from `ImmutableException`, which itself extends PHP's `RuntimeException`:

```
RuntimeException
└── ImmutableException (abstract)
    ├── PropertyDoesNotExistException
    ├── ReadOnlyPropertyException
    ├── InvalidTypeException
    └── UnsupportedTypeException
```

## ImmutableException

The abstract base class for all package exceptions. Use this for catch-all handling:

```php
use Cline\Immutable\Exceptions\ImmutableException;

try {
    $updated = $user->mutate(['invalid' => 'data']);
} catch (ImmutableException $e) {
    // Handles any mutation error
    log_error($e->getMessage());
}
```

## PropertyDoesNotExistException

Thrown when attempting to mutate a property that doesn't exist on the object.

### Factory Method

```php
PropertyDoesNotExistException::forProperty(
    string $property,
    string $class
): self
```

### Example

```php
use Cline\Immutable\Exceptions\PropertyDoesNotExistException;

$user = new UserData('John', 'john@example.com', 30);

try {
    $user->mutate(['nonexistent' => 'value']);
} catch (PropertyDoesNotExistException $e) {
    $e->getMessage();
    // 'Property "nonexistent" does not exist on class "UserData".'
}
```

### When It's Thrown

- Passing a property name that doesn't exist on the object
- Typos in property names
- Attempting to set properties from a parent class that don't exist

## ReadOnlyPropertyException

Thrown when attempting to mutate a property that is marked as read-only and cannot be changed.

### Factory Method

```php
ReadOnlyPropertyException::forProperty(
    string $property,
    string $class
): self
```

### Example

```php
use Cline\Immutable\Exceptions\ReadOnlyPropertyException;

try {
    $user->mutate(['id' => 'new-id']);
} catch (ReadOnlyPropertyException $e) {
    $e->getMessage();
    // 'Property "id" on class "UserData" is read-only and cannot be mutated.'
}
```

### When It's Thrown

- Attempting to mutate a property that has been explicitly marked as immutable
- Trying to change properties that should never change after construction

## InvalidTypeException

Thrown when the value type doesn't match the property's declared type.

### Factory Method

```php
InvalidTypeException::mismatch(
    string $property,
    string $expectedType,
    mixed $actualValue
): self
```

### Example

```php
use Cline\Immutable\Exceptions\InvalidTypeException;

$user = new UserData('John', 'john@example.com', 30);

try {
    $user->mutate(['age' => 'thirty']);
} catch (InvalidTypeException $e) {
    $e->getMessage();
    // 'Property "age" expects type "int", got "string".'
}
```

### When It's Thrown

- Passing a string where int is expected
- Passing wrong class type for object properties
- Passing wrong enum case type
- Null for non-nullable properties

### Type Validation Rules

| Property Type | Valid Values |
|--------------|--------------|
| `int` | Integers only |
| `float` | Floats and integers |
| `string` | Strings only |
| `bool` | Booleans only |
| `array` | Arrays only |
| `object` | Any object |
| `mixed` | Any value |
| `?type` | Type or null |
| Class name | Instance of that class |

Union and intersection types skip validation.

## UnsupportedTypeException

Thrown when attempting to mutate an unsupported value type.

### Factory Method

```php
UnsupportedTypeException::forValue(mixed $value): self
```

### Example

```php
use Cline\Immutable\Exceptions\UnsupportedTypeException;
use Cline\Immutable\Mutator;

try {
    // This would fail if passed a non-object/non-array
    Mutator::mutate('string', ['key' => 'value']);
} catch (UnsupportedTypeException $e) {
    $e->getMessage();
    // 'Cannot mutate value of type "string". Supported types: object, array.'
}
```

### Supported Types

The Mutator supports:
- Arrays
- Objects (any type)
- stdClass

It does not support scalar types (string, int, float, bool) or resources.

## Error Handling Patterns

### Specific Exception Handling

```php
use Cline\Immutable\Exceptions\PropertyDoesNotExistException;
use Cline\Immutable\Exceptions\InvalidTypeException;

try {
    $updated = $user->mutate($changes);
} catch (PropertyDoesNotExistException $e) {
    // Handle unknown property
    throw new ValidationException("Unknown field: " . $e->getMessage());
} catch (InvalidTypeException $e) {
    // Handle type error
    throw new ValidationException("Invalid type: " . $e->getMessage());
}
```

### Validation Before Mutation

For user input, validate before mutating:

```php
$validated = $request->validate([
    'name' => 'string|max:255',
    'age' => 'integer|min:0',
]);

// Now safe to mutate
$updated = $user->mutate($validated);
```

### Logging Errors

```php
use Cline\Immutable\Exceptions\ImmutableException;

try {
    $updated = $user->mutate($data);
} catch (ImmutableException $e) {
    Log::warning('Mutation failed', [
        'class' => get_class($user),
        'data' => $data,
        'error' => $e->getMessage(),
    ]);
    throw $e;
}
```

<a id="doc-docs-static-mutations"></a>

## Overview

The `Mutator` class provides static methods to create modified copies of any object or array. Use it when working with third-party classes, `stdClass`, or when you need a standalone mutation utility.

## Static API

### mutate()

The simplest way to create a modified copy:

```php
<?php

use Cline\Immutable\Mutator;

// Mutate an array
$original = ['name' => 'John', 'age' => 30];
$updated = Mutator::mutate($original, ['age' => 31]);

// Original unchanged
$original; // ['name' => 'John', 'age' => 30]
$updated;  // ['name' => 'John', 'age' => 31]

// Mutate an object
$user = new UserData('John', 'john@example.com', 30);
$updated = Mutator::mutate($user, ['age' => 31]);
```

## Fluent API

For more complex mutations, use the fluent interface:

### for()

Start a fluent chain:

```php
$mutator = Mutator::for($original);
```

### with()

Add multiple properties to modify:

```php
$updated = Mutator::for($user)
    ->with(['name' => 'Jane', 'age' => 25])
    ->get();
```

### set()

Add a single property:

```php
$updated = Mutator::for($user)
    ->set('name', 'Jane')
    ->get();
```

### Chaining

Chain multiple calls together:

```php
$updated = Mutator::for($user)
    ->set('name', 'Jane')
    ->with(['email' => 'jane@example.com'])
    ->set('age', 25)
    ->get();
```

Later mutations override earlier ones:

```php
$updated = Mutator::for(['name' => 'John'])
    ->set('name', 'Jane')
    ->set('name', 'Bob')
    ->get();

$updated['name']; // 'Bob'
```

### get()

Execute the mutation and return the result:

```php
$result = Mutator::for($data)->with($changes)->get();
```

## Array Mutations

### Replace Values

```php
$original = ['a' => 1, 'b' => 2];
$updated = Mutator::mutate($original, ['a' => 10]);
// ['a' => 10, 'b' => 2]
```

### Add New Keys

```php
$original = ['a' => 1, 'b' => 2];
$updated = Mutator::mutate($original, ['c' => 3]);
// ['a' => 1, 'b' => 2, 'c' => 3]
```

### Numeric Keys

```php
$original = [1, 2, 3];
$updated = Mutator::mutate($original, [0 => 10]);
// [10, 2, 3]
```

### Nested Arrays

Replace entire nested structures:

```php
$original = ['user' => ['name' => 'John', 'age' => 30]];
$updated = Mutator::mutate($original, [
    'user' => ['name' => 'Jane', 'age' => 25]
]);
// ['user' => ['name' => 'Jane', 'age' => 25]]
```

## Object Mutations

### Objects with Immutable Trait

The Mutator automatically delegates to the object's `mutate()` method:

```php
$user = new UserData('John', 'john@example.com', 30);
$updated = Mutator::mutate($user, ['age' => 31]);

// Equivalent to:
$updated = $user->mutate(['age' => 31]);
```

### Generic Objects

Works with any object using reflection:

```php
class GenericObject
{
    public function __construct(
        public string $name,
        public int $value,
    ) {}
}

$obj = new GenericObject('test', 42);
$updated = Mutator::mutate($obj, ['value' => 100]);

$obj->value;     // 42
$updated->value; // 100
```

### stdClass Objects

Full support for dynamic objects:

```php
$obj = new stdClass();
$obj->name = 'John';
$obj->age = 30;

$updated = Mutator::mutate($obj, ['age' => 31]);
$updated->age; // 31

// Can add new properties
$updated = Mutator::mutate($obj, ['email' => 'john@example.com']);
$updated->email; // 'john@example.com'
```

## Type Validation

For objects with type hints, the Mutator validates values:

```php
use Cline\Immutable\Exceptions\InvalidTypeException;

$obj = new GenericObject('test', 42);

// Throws InvalidTypeException
Mutator::mutate($obj, ['value' => 'not an int']);
```

### Property Validation

```php
use Cline\Immutable\Exceptions\PropertyDoesNotExistException;

$obj = new GenericObject('test', 42);

// Throws PropertyDoesNotExistException
Mutator::mutate($obj, ['nonexistent' => 'value']);
```

Note: `stdClass` allows any property name without validation.

## Complex Example

Real-world usage with operation state transitions:

```php
$operation = new OperationData(
    id: 'op-123',
    function: 'process',
    version: '1.0.0',
    status: OperationStatus::Running,
    progress: 50,
    result: null,
    errors: [],
    startedAt: $now,
    completedAt: null,
    cancelledAt: null,
    metadata: ['key' => 'value'],
);

// Without immutable mutation (verbose):
$cancelledOperation = new OperationData(
    id: $operation->id,
    function: $operation->function,
    version: $operation->version,
    status: OperationStatus::Cancelled,
    progress: $operation->progress,
    result: $operation->result,
    errors: $operation->errors,
    startedAt: $operation->startedAt,
    completedAt: $operation->completedAt,
    cancelledAt: new DateTimeImmutable(),
    metadata: $operation->metadata,
);

// With Mutator (clean):
$cancelledOperation = Mutator::mutate($operation, [
    'status' => OperationStatus::Cancelled,
    'cancelledAt' => new DateTimeImmutable(),
]);
```

## Laravel Facade

For Laravel applications, use the facade:

```php
<?php

use Cline\Immutable\Facades\Mutator;

$updated = Mutator::mutate($user, ['age' => 31]);

$updated = Mutator::for($user)
    ->with(['age' => 31])
    ->get();
```

<a id="doc-docs-trait-usage"></a>

## Overview

The `Immutable` trait provides a `mutate()` method that creates modified copies of your objects while keeping the originals unchanged. This is the recommended approach when you control the class definition.

## Basic Setup

Add the trait and implement the `Mutable` contract:

```php
<?php

use Cline\Immutable\Concerns\Immutable;
use Cline\Immutable\Contracts\Mutable;

final class UserData implements Mutable
{
    use Immutable;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly int $age,
    ) {}
}
```

The `Mutable` contract is optional but recommended - it documents that your class supports immutable mutation and enables IDE autocompletion.

## Mutation Patterns

### Single Property

```php
$user = new UserData('John', 'john@example.com', 30);
$updated = $user->mutate(['age' => 31]);

// Original unchanged
$user->age;     // 30
$updated->age;  // 31
```

### Multiple Properties

```php
$updated = $user->mutate([
    'name' => 'Jane',
    'email' => 'jane@example.com',
]);
```

### All Properties

```php
$updated = $user->mutate([
    'name' => 'Jane',
    'email' => 'jane@example.com',
    'age' => 25,
]);
```

### Clone Without Changes

Pass an empty array to create an exact clone:

```php
$clone = $user->mutate([]);

$clone !== $user;          // true (different instance)
$clone->name === $user->name;  // true (same values)
```

## Complex Objects

The trait handles complex property types including enums, DateTimeImmutable, and nested objects.

### With Enums

```php
<?php

enum OperationStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}

final class OperationData implements Mutable
{
    use Immutable;

    public function __construct(
        public readonly string $id,
        public readonly OperationStatus $status,
        public readonly int $progress,
        public readonly ?DateTimeImmutable $completedAt,
    ) {}
}

$operation = new OperationData(
    id: 'op-123',
    status: OperationStatus::Running,
    progress: 50,
    completedAt: null,
);

$completed = $operation->mutate([
    'status' => OperationStatus::Completed,
    'progress' => 100,
    'completedAt' => new DateTimeImmutable(),
]);
```

### Nullable Properties

```php
// Set nullable to value
$withDate = $operation->mutate(['completedAt' => new DateTimeImmutable()]);

// Set back to null
$withoutDate = $withDate->mutate(['completedAt' => null]);
```

## Type Validation

The trait validates property types at runtime:

```php
use Cline\Immutable\Exceptions\InvalidTypeException;

// Throws InvalidTypeException - age expects int
$user->mutate(['age' => 'thirty']);
// Message: Property "age" expects type "int", got "string".

// Throws InvalidTypeException - status expects enum
$operation->mutate(['status' => 'running']);
// Message: Property "status" expects type "OperationStatus", got "string".
```

### Supported Types

- Scalar types: `int`, `float`, `string`, `bool`
- `array`
- `object`
- `mixed`
- Class types (including enums)
- Nullable types (`?string`, `?int`, etc.)

Union and intersection types skip validation.

## Property Validation

Non-existent properties throw an exception:

```php
use Cline\Immutable\Exceptions\PropertyDoesNotExistException;

$user->mutate(['nonexistent' => 'value']);
// Throws: Property "nonexistent" does not exist on class "UserData".
```

## Best Practices

### Use Readonly Properties

Combine with PHP's `readonly` keyword for maximum safety:

```php
public function __construct(
    public readonly string $name,  // Cannot be modified after construction
    public readonly string $email,
    public readonly int $age,
) {}
```

### Final Classes

Make classes `final` to prevent inheritance issues:

```php
final class UserData implements Mutable
{
    use Immutable;
    // ...
}
```

### Descriptive DTOs

Use Data Transfer Objects (DTOs) for complex state:

```php
final class OrderData implements Mutable
{
    use Immutable;

    public function __construct(
        public readonly string $id,
        public readonly string $customerId,
        public readonly OrderStatus $status,
        public readonly Money $total,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $shippedAt,
        public readonly ?string $trackingNumber,
    ) {}
}

// Clean state transitions
$shipped = $order->mutate([
    'status' => OrderStatus::Shipped,
    'shippedAt' => new DateTimeImmutable(),
    'trackingNumber' => 'TRACK123',
]);
```
