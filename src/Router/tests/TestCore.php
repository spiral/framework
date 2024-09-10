<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Core\CoreInterface;

class TestCore implements CoreInterface
{
    public function __construct(private readonly CoreInterface $core)
    {
    }

    public function callAction(string $controller, string $action = null, array $parameters = []): string
    {
        return '@wrapped.' . $this->core->callAction($controller, $action, $parameters);
    }
}
