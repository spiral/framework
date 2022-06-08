<?php

declare(strict_types=1);

namespace Spiral\Router\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Bootloader\Http\RouterBootloader;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Router\Command\ResetCommand;
use Spiral\Router\GroupRegistry;
use Spiral\Router\Route;
use Spiral\Router\RouteLocator;
use Spiral\Router\Target\Action;

/**
 * Configures application routes using annotations and pre-defined configuration groups.
 */
final class AnnotatedRoutesBootloader extends Bootloader implements SingletonInterface
{
    public const MEMORY_SECTION = 'routes';

    protected const DEPENDENCIES = [
        RouterBootloader::class,
        AttributesBootloader::class,
    ];

    public function __construct(
        private readonly MemoryInterface $memory,
    ) {
    }

    public function init(ConsoleBootloader $console): void
    {
        $console->addCommand(ResetCommand::class);
    }

    public function boot(EnvironmentInterface $env, RouteLocator $locator, GroupRegistry $groups): void
    {
        $cached = $env->get('ROUTE_CACHE', !$env->get('DEBUG'));

        $schema = $this->memory->loadData(self::MEMORY_SECTION);
        if (empty($schema) || !$cached) {
            $schema = $locator->findDeclarations();
            $this->memory->saveData(self::MEMORY_SECTION, $schema);
        }

        $this->configureRoutes($schema, $groups);
    }

    private function configureRoutes(array $routes, GroupRegistry $groups): void
    {
        foreach ($routes as $name => $schema) {
            $route = new Route(
                $schema['pattern'],
                new Action($schema['controller'], $schema['action']),
                $schema['defaults']
            );

            $groups
                ->getGroup($schema['group'])
                ->addRoute($name, $route->withVerbs(...$schema['verbs'])->withMiddleware(...$schema['middleware']));
        }
    }
}
