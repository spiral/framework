<?php

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Psr\Container\ContainerInterface;
use Spiral\Core\Attribute\Singleton;
use Spiral\Router\Exception\RouterException;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\RouterInterface;
use Spiral\Stempler\Exception\DirectiveException;
use Spiral\Stempler\Node\Dynamic\Directive;

#[Singleton]
final class RouteDirective extends AbstractDirective
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
        parent::__construct();
    }

    /**
     * Injects service into template.
     */
    public function renderRoute(Directive $directive): string
    {
        if (\count($directive->values) < 1) {
            throw new DirectiveException(
                'Unable to call @route directive, at least 1 value is required',
                $directive->getContext()
            );
        }

        return \sprintf(
            '<?php echo $this->container->get(\Spiral\Stempler\Directive\RouteDirective::class)->uri(%s); ?>',
            $directive->body
        );
    }

    /**
     * Provides the ability to inject templated args in a form or {id} or {{id}}.
     */
    public function uri(string $route, array $params = []): string
    {
        $vars = [];
        $restore = [];
        foreach ($params as $key => $value) {
            if (\is_string($value) && \preg_match('#\{.*\}#', $value)) {
                $restore[\sprintf('__%s__', $key)] = $value;
                $value = \sprintf('__%s__', $key);
            }

            $vars[$key] = $value;
        }

        try {
            return \strtr(
                $this->container->get(RouterInterface::class)->uri($route, $vars)->__toString(),
                $restore
            );
        } catch (UndefinedRouteException $e) {
            throw new RouterException("No such route {$route}", $e->getCode(), $e);
        }
    }
}
