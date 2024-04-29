<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Exception\ControllerException;
use Spiral\Interceptors\HandlerInterface;

/**
 * General application enterpoint class.
 *
 * @deprecated Use {@see HandlerInterface} instead.
 */
interface CoreInterface
{
    /**
     * Request specific action result from Core. Due in 99% every action will need parent
     * controller, we can request it too.
     *
     * @param string $controller Controller class.
     * @param string $action Controller method name.
     * @param array $parameters Action parameters (if any).
     *
     * @throws ControllerException
     * @throws \Throwable
     */
    public function callAction(string $controller, string $action, array $parameters = []): mixed;
}
