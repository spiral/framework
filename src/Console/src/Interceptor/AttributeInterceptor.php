<?php

declare(strict_types=1);

namespace Spiral\Console\Interceptor;

use Spiral\Console\Command;
use Spiral\Console\Configurator\Attribute\Parser;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Symfony\Component\Console\Input\InputInterface;

final class AttributeInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly Parser $parser
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): int
    {
        \assert($parameters['input'] instanceof InputInterface);
        \assert($parameters['command'] instanceof Command);

        $this->parser->fillProperties($parameters['command'], $parameters['input']);

        return $core->callAction($controller, $action, $parameters);
    }
}
