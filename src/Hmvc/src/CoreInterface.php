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
 * General application enterpoint class.
 */
interface CoreInterface
{
    /**
     * Request specific action result from Core. Due in 99% every action will need parent
     * controller, we can request it too.
     *
     * @param string $controller Controller class.
     * @param string $action     Controller method name.
     * @param array  $parameters Action parameters (if any).
     * @return mixed
     *
     * @throws ControllerException
     * @throws \Throwable
     */
    public function callAction(string $controller, string $action, array $parameters = []);
}
