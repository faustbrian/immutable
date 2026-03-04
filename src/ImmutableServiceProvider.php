<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Immutable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Service provider for the Immutable package.
 *
 * Registers the Mutator class in the Laravel container.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ImmutableServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package settings.
     *
     * @param Package $package The package configuration instance
     */
    public function configurePackage(Package $package): void
    {
        $package->name('immutable');
    }
}
