<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Exception\ControllerException;

/**
 * Provides the ability to intercept and wrap the call to the domain core with all the call context.
 */
interface CoreInterceptorInterface
{
    /**
     * Process action request to underlying domain core action.
     *
     * @throws ControllerException
     * @throws \Throwable
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed;
}
