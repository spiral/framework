<?php

declare(strict_types=1);

namespace Spiral\Command\Router;

use Spiral\Boot\KernelInterface;
use Spiral\Console\Command;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Router\GroupRegistry;
use Spiral\Router\Route;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Action;
use Spiral\Router\Target\Controller;
use Spiral\Router\Target\Group;
use Spiral\Router\Target\Namespaced;

final class ListCommand extends Command implements SingletonInterface
{
    protected const NAME = 'route:list';
    protected const DESCRIPTION = 'List application routes';

    /**
     * @throws \ReflectionException
     */
    public function perform(RouterInterface $router, GroupRegistry $registry, KernelInterface $kernel): int
    {
        $grid = $this->table(['Name:', 'Verbs:', 'Pattern:', 'Target:', 'Group:']);

        foreach ($router->getRoutes() as $name => $route) {
            if ($route instanceof Route) {
                $grid->addRow(
                    [
                        $name,
                        $this->getVerbs($route),
                        $this->getPattern($route),
                        $this->getTarget($route, $kernel),
                        \implode(', ', $this->getRouteGroups($registry, $name)),
                    ]
                );
            }
        }

        $grid->render();

        return self::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function getRouteGroups(GroupRegistry $registry, string $routeName): array
    {
        $groups = [];
        foreach ($registry as $groupName => $group) {
            if ($group->hasRoute($routeName)) {
                $groups[] = $groupName;
            }
        }

        return $groups;
    }

    private function getVerbs(Route $route): string
    {
        if ($route->getVerbs() === Route::VERBS) {
            return '*';
        }

        $result = [];
        foreach ($route->getVerbs() as $verb) {
            $result[] = match (\strtolower($verb)) {
                'get' => '<fg=green>GET</>',
                'post' => '<fg=blue>POST</>',
                'put' => '<fg=yellow>PUT</>',
                'delete' => '<fg=red>DELETE</>'
            };
        }

        return \implode(', ', $result);
    }

    private function getPattern(Route $route): string
    {
        $pattern = $this->getValue($route->getUriHandler(), 'pattern');
        $pattern = \str_replace(
            '[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}',
            'uuid',
            $pattern
        );

        return \preg_replace_callback(
            '/<([^>]*)>/',
            static fn($m) => \sprintf('<fg=magenta>%s</>', $m[0]),
            $pattern
        );
    }

    /**
     *
     * @throws \ReflectionException
     */
    private function getTarget(Route $route, KernelInterface $kernel): string
    {
        $target = $this->getValue($route, 'target');
        switch (true) {
            case $target instanceof \Closure:
                $reflection = new \ReflectionFunction($target);

                return \sprintf(
                    'Closure(%s:%s)',
                    \basename($reflection->getFileName()),
                    $reflection->getStartLine()
                );

            case $target instanceof Action:
                return \sprintf(
                    '%s->%s',
                    $this->relativeClass($this->getValue($target, 'controller'), $kernel),
                    \implode('|', (array)$this->getValue($target, 'action'))
                );

            case $target instanceof Controller:
                return \sprintf(
                    '%s->*',
                    $this->relativeClass($this->getValue($target, 'controller'), $kernel)
                );

            case $target instanceof Group:
                $result = [];
                foreach ($this->getValue($target, 'controllers') as $alias => $class) {
                    $result[] = \sprintf('%s => %s', $alias, $this->relativeClass($class, $kernel));
                }

                return \implode("\n", $result);

            case $target instanceof Namespaced:
                return \sprintf(
                    '%s\*%s->*',
                    $this->relativeClass($this->getValue($target, 'namespace'), $kernel),
                    $this->getValue($target, 'postfix')
                );
            default:
                return $target::class;
        }
    }

    private function getValue(object $object, string $property): mixed
    {
        try {
            $r = new \ReflectionObject($object);
            $prop = $r->getProperty($property);
        } catch (\Throwable $e) {
            return $e->getMessage();
        }

        return $prop->getValue($object);
    }

    private function relativeClass(string $class, KernelInterface $kernel): string
    {
        $r = new \ReflectionObject($kernel);

        if (\str_starts_with($class, $r->getNamespaceName())) {
            return \substr($class, \strlen($r->getNamespaceName()) + 1);
        }

        return $class;
    }
}
