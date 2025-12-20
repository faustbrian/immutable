<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Immutable\Contracts;

/**
 * Contract for objects that support immutable mutation.
 *
 * Objects implementing this interface can create modified copies
 * of themselves without changing the original instance.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface Mutable
{
    /**
     * Create a new instance with the specified properties modified.
     *
     * @param  array<string, mixed> $properties Properties to modify in the new instance
     * @return static               A new instance with the modified properties
     */
    public function mutate(array $properties): static;
}
