<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Unit\Stub\Legacy;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;

final class LegacyExceptionInterceptor implements CoreInterceptorInterface
{
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        throw new \RuntimeException('test');
    }
}
