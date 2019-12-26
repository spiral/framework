<?php

declare(strict_types=1);

namespace Spiral\Command\Router;

use Spiral\Console\Command;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Router\Route;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Action;
use Symfony\Component\Console\Helper\Table;

class DebugCommand extends Command implements SingletonInterface
{
    protected const NAME = 'route:list';
    protected const DESCRIPTION = 'List application routes';

    /**
     * @param RouterInterface $router
     */
    public function perform(RouterInterface $router): void
    {
        $grid = $this->table(['Verbs:', 'Pattern:', 'Target:']);

        foreach ($this->getRoutes($router) as $name => $route) {
            if ($route instanceof Route) {
                $this->renderRoute($grid, $name, $route);
            }
        }

        $grid->render();
    }

    /**
     * @param Table $table
     * @param string $name
     * @param Route $route
     */
    private function renderRoute(Table $table, string $name, Route $route)
    {
        $table->addRow(
            [
                $this->getVerbs($route),
                $this->getPattern($route),
                $this->getTarget($route)
            ]
        );
    }

    /**
     * @param RouterInterface $router
     * @return \Generator|null
     */
    private function getRoutes(RouterInterface $router): ?\Generator
    {
        yield from $router->getRoutes();
    }

    /**
     * @param Route $route
     * @return string
     */
    private function getVerbs(Route $route): string
    {
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
                return sprintf('<fg=yellow>%s</>', $m[0]);
            },
            $pattern
        );
    }

    /**
     * @param Route $route
     * @return string
     */
    private function getTarget(Route $route): string
    {
        $target = $this->getValue($route, 'target');
        switch (true) {
            case $target instanceof Action:
                return sprintf(
                    '%s::%s',
                    $this->getValue($target, 'controller'),
                    $this->getValue($target, 'action')
                );
        }

        return '';
        return $this->getValue($route->getUriHandler(), 'pattern');
    }

    /**
     * @param object $object
     * @param string $property
     * @return mixed
     */
    private function getValue(object $object, string $property)
    {
        try {
            $r = new \ReflectionObject($object);
            $prop = $r->getProperty($property);
            $prop->setAccessible(true);
        } catch (\Throwable $e) {
            return $e->getMessage();
        }

        return $prop->getValue($object);
    }
}
