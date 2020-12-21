<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Router\Exception\RouterException;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\RouterInterface;
use Spiral\Stempler\Exception\DirectiveException;
use Spiral\Stempler\Node\Dynamic\Directive;

final class RouteDirective extends AbstractDirective implements SingletonInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    /**
     * Injects service into template.
     *
     * @param Directive $directive
     * @return string
     */
    public function renderRoute(Directive $directive): string
    {
        if (count($directive->values) < 1) {
            throw new DirectiveException(
                'Unable to call @route directive, at least 1 value is required',
                $directive->getContext()
            );
        }

        return sprintf(
            '<?php echo $this->container->get(\Spiral\Stempler\Directive\RouteDirective::class)->uri(%s); ?>',
            $directive->body
        );
    }

    /**
     * Provides the ability to inject templated args in a form or {id} or {{id}}.
     *
     * @param string $route
     * @param array  $params
     * @return string
     */
    public function uri(string $route, array $params = []): string
    {
        $vars = [];
        $restore = [];
        foreach ($params as $key => $value) {
            if (is_string($value) && preg_match('/\{.*\}/', $value)) {
                $restore[sprintf('__%s__', $key)] = $value;
                $value = sprintf('__%s__', $key);
            }

            $vars[$key] = $value;
        }

        try {
            return strtr(
                $this->container->get(RouterInterface::class)->uri($route, $vars)->__toString(),
                $restore
            );
        } catch (UndefinedRouteException $e) {
            throw new RouterException("No such route {$route}", $e->getCode(), $e);
        }
    }
}
