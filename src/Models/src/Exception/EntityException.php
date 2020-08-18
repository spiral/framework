<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Models\Exception;

/**
 * Errors raised by Entity logic in runtime.
 */
class EntityException extends \RuntimeException implements EntityExceptionInterface
{
}
