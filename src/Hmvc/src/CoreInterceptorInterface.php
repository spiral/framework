<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
     * @param string        $controller
     * @param string        $action
     * @param array         $parameters
     * @param CoreInterface $core
     * @return mixed
     *
     * @throws ControllerException
     * @throws \Throwable
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core);
}
