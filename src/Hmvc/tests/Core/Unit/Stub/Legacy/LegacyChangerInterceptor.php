<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Unit\Stub\Legacy;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;

final class LegacyChangerInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly ?string $controller = null,
        private readonly ?string $action = null,
        private readonly ?array $parameters = null,
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        return $core->callAction(
            $this->controller ?? $controller,
            $this->action ?? $action,
            $this->parameters ?? $parameters,
        );
    }
}
