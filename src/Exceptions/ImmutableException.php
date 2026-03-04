<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Immutable\Exceptions;

use RuntimeException;

/**
 * Base exception for all immutable mutation errors.
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class ImmutableException extends RuntimeException {}
