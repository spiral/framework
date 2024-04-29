<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Unit\Stub\Legacy;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;

final class LegacyStatefulInterceptor implements CoreInterceptorInterface
{
    public string $controller;
    public string $action;
    public array $parameters;
    public CoreInterface $next;
    public mixed $result;

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->parameters = $parameters;
        $this->next = $core;
        return $this->result = $core->callAction($controller, $action, $parameters);
    }
}
