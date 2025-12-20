[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

# Immutable

Immutable object mutation utilities for PHP - create modified copies without changing originals.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

```bash
composer require cline/immutable
```

## Usage

### Using the Immutable Trait

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
$updated = $user->mutate(['age' => 31]);

$user->age;     // 30 (unchanged)
$updated->age;  // 31 (new instance)
```

### Using the Mutator Class

```php
<?php

use Cline\Immutable\Mutator;

// Mutate arrays
$updated = Mutator::mutate(['name' => 'John'], ['name' => 'Jane']);

// Mutate any object
$updated = Mutator::mutate($user, ['age' => 31]);

// Fluent API
$updated = Mutator::for($user)
    ->with(['name' => 'Jane'])
    ->set('age', 25)
    ->get();
```

## Documentation

- **[Getting Started](https://docs.cline.sh/immutable/getting-started/)** - Installation and quick start
- **[Trait Usage](https://docs.cline.sh/immutable/trait-usage/)** - Using the Immutable trait
- **[Static Mutations](https://docs.cline.sh/immutable/static-mutations/)** - Arrays and generic objects
- **[Exceptions](https://docs.cline.sh/immutable/exceptions/)** - Error handling

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please use the [GitHub security reporting form][link-security] rather than the issue queue.

## Credits

- [Brian Faust][link-maintainer]
- [All Contributors][link-contributors]

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

[ico-tests]: https://github.com/faustbrian/immutable/actions/workflows/quality-assurance.yaml/badge.svg
[ico-version]: https://img.shields.io/packagist/v/cline/immutable.svg
[ico-license]: https://img.shields.io/badge/License-MIT-green.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cline/immutable.svg

[link-tests]: https://github.com/faustbrian/immutable/actions
[link-packagist]: https://packagist.org/packages/cline/immutable
[link-downloads]: https://packagist.org/packages/cline/immutable
[link-security]: https://github.com/faustbrian/immutable/security
[link-maintainer]: https://github.com/faustbrian
[link-contributors]: ../../contributors
