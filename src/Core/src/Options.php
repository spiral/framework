<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Exception\Scope\BadScopeException;

class Options
{
    /**
     * Enables checking of scopes when creating an object. If the check is enabled and the object is created outside
     * the required scope, an exception {@see BadScopeException} will be thrown.
     * By default, checking is disabled. Will be enabled by default in version 4.0
     */
    public bool $checkScope = false;
}
