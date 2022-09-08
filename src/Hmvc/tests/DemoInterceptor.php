<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;

class DemoInterceptor implements CoreInterceptorInterface
{
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): string
    {
        return '?' . $core->callAction($controller, $action, $parameters) . '!';
    }
}
