<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Immutable\Facades;

use Cline\Immutable\Mutator as MutatorClass;
use Illuminate\Support\Facades\Facade;
use Override;

/**
 * Facade for the Mutator class.
 *
 * @method static MutatorClass                for(array<string, mixed>|object $value)
 * @method static array<string, mixed>|object mutate(array<string, mixed>|object $value, array<string, mixed> $properties)
 *
 * @author Brian Faust <brian@cline.sh>
 * @see MutatorClass
 */
final class Mutator extends Facade
{
    /**
     * Get the registered name of the component.
     */
    #[Override()]
    protected static function getFacadeAccessor(): string
    {
        return MutatorClass::class;
    }
}
