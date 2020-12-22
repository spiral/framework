<?php

declare(strict_types=1);

namespace Spiral\App\Interceptor;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;

class Five implements CoreInterceptorInterface
{
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): array
    {
        $append = new Append('five');
        return $append->process($controller, $action, $parameters, $core);
    }
}
