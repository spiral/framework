<?php

declare(strict_types=1);

namespace Spiral\Core\Exception;

/**
 * Raised in cases when "sugar" code can not be executed (for example there is no shared/static
 * container but developer's code is using shorted code version).
 *
 * Sugar conditions are avoidable.
 */
class ScopeException extends LogicException
{
}
