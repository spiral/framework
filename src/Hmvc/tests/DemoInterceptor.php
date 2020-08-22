<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Core;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;

class DemoInterceptor implements CoreInterceptorInterface
{
    public function process(string $controller, string $action, array $parameters, CoreInterface $core)
    {
        return '?' . $core->callAction($controller, $action, $parameters) . '!';
    }
}
