<?php

declare(strict_types=1);

namespace Spiral\App\Interceptor;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;

class Three implements CoreInterceptorInterface
{
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): array
    {
        $append = new Append('three');
        return $append->process($controller, $action, $parameters, $core);
    }
}
