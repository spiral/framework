<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Alexander Novikov
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Command\Router;

use Closure;
use ReflectionException;
use ReflectionFunction;
use ReflectionObject;
use Spiral\Boot\KernelInterface;
use Spiral\Console\Command;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Router\Route;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Action;
use Spiral\Router\Target\Controller;
use Spiral\Router\Target\Group;
use Spiral\Router\Target\Namespaced;
use Throwable;

final class ListCommand extends Command implements SingletonInterface
{
    protected const NAME        = 'route:list';
    protected const DESCRIPTION = 'List application routes';

    /**
     * @param RouterInterface $router
     * @param KernelInterface $kernel
     *
     * @throws ReflectionException
     */
    public function perform(RouterInterface $router, KernelInterface $kernel): void
    {
        $grid = $this->table(['Verbs:', 'Pattern:', 'Target:']);

        foreach ($router->getRoutes() as $route) {
            if ($route instanceof Route) {
                $grid->addRow(
                    [
                        $this->getVerbs($route),
                        $this->getPattern($route),
                        $this->getTarget($route, $kernel)
                    ]
                );
            }
        }

        $grid->render();
    }

    /**
     * @param Route $route
     * @return string
     */
    private function getVerbs(Route $route): string
    {
        if ($route->getVerbs() === Route::VERBS) {
            return '*';
        }

        $result = [];

        foreach ($route->getVerbs() as $verb) {
            switch (strtolower($verb)) {
                case 'get':
                    $verb = '<fg=green>GET</>';
                    break;
                case 'post':
                    $verb = '<fg=blue>POST</>';
                    break;
                case 'put':
                    $verb = '<fg=yellow>PUT</>';
                    break;
                case 'delete':
                    $verb = '<fg=red>DELETE</>';
                    break;
            }

            $result[] = $verb;
        }

        return implode(', ', $result);
    }

    /**
     * @param Route $route
     * @return string
     */
    private function getPattern(Route $route): string
    {
        $pattern = $this->getValue($route->getUriHandler(), 'pattern');
        $pattern = str_replace(
            '[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}',
            'uuid',
            $pattern
        );

        return preg_replace_callback(
            '/<([^>]*)>/',
            static function ($m) {
                return sprintf('<fg=magenta>%s</>', $m[0]);
            },
            $pattern
        );
    }

    /**
     * @param Route           $route
     * @param KernelInterface $kernel
     * @return string
     *
     * @throws ReflectionException
     */
    private function getTarget(Route $route, KernelInterface $kernel): string
    {
        $target = $this->getValue($route, 'target');
        switch (true) {
            case $target instanceof Closure:
                $reflection = new ReflectionFunction($target);
                return sprintf(
                    'Closure(%s:%s)',
                    basename($reflection->getFileName()),
                    $reflection->getStartLine()
                );

            case $target instanceof Action:
                return sprintf(
                    '%s->%s',
                    $this->relativeClass($this->getValue($target, 'controller'), $kernel),
                    $this->getValue($target, 'action')
                );

            case $target instanceof Controller:
                return sprintf(
                    '%s->*',
                    $this->relativeClass($this->getValue($target, 'controller'), $kernel)
                );

            case $target instanceof Group:
                $result = [];
                foreach ($this->getValue($target, 'controllers') as $alias => $class) {
                    $result[] = sprintf('%s => %s', $alias, $this->relativeClass($class, $kernel));
                }

                return implode("\n", $result);

            case $target instanceof Namespaced:
                return sprintf(
                    '%s\*%s->*',
                    $this->relativeClass($this->getValue($target, 'namespace'), $kernel),
                    $this->getValue($target, 'postfix')
                );
        }

        return '';
    }

    /**
     * @param object $object
     * @param string $property
     * @return mixed
     */
    private function getValue(object $object, string $property)
    {
        try {
            $r = new ReflectionObject($object);
            $prop = $r->getProperty($property);
            $prop->setAccessible(true);
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        return $prop->getValue($object);
    }

    /**
     * @param string          $class
     * @param KernelInterface $kernel
     * @return string
     */
    private function relativeClass(string $class, KernelInterface $kernel): string
    {
        $r = new ReflectionObject($kernel);
        $r->getNamespaceName();

        if (strpos($class, $r->getNamespaceName()) === 0) {
            return substr($class, strlen($r->getNamespaceName()) + 1);
        }

        return $class;
    }
}
