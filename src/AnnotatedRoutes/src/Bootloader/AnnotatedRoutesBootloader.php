<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Bootloader;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\ConsoleBootloader;
use Spiral\Bootloader\Http\RouterBootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Router\Command\ResetCommand;
use Spiral\Router\GroupRegistry;
use Spiral\Router\RouteLocator;

/**
 * Configures application routes using annotations and pre-defined configuration groups.
 */
final class AnnotatedRoutesBootloader extends Bootloader implements SingletonInterface
{
    public const MEMORY_SECTION = 'routes';

    protected const DEPENDENCIES = [
        RouterBootloader::class,
        ConsoleBootloader::class,
    ];

    protected const SINGLETONS = [
        GroupRegistry::class => [self::class, 'getGroups'],
    ];

    /** @var MemoryInterface */
    private $memory;

    /** @var GroupRegistry */
    private $groups;

    /**
     * @param MemoryInterface $memory
     * @param GroupRegistry   $groupRegistry
     */
    public function __construct(MemoryInterface $memory, GroupRegistry $groupRegistry)
    {
        $this->memory = $memory;
        $this->groups = $groupRegistry;
    }

    /**
     * @param EnvironmentInterface $env
     * @param ConsoleBootloader    $console
     * @param RouteLocator         $locator
     */
    public function boot(ConsoleBootloader $console, EnvironmentInterface $env, RouteLocator $locator): void
    {
        $console->addCommand(ResetCommand::class);

        $cached = $env->get('ROUTE_CACHE', !$env->get('DEBUG'));
        AnnotationRegistry::registerLoader('class_exists');

        $schema = $this->memory->loadData(self::MEMORY_SECTION);
        if (empty($schema) || !$cached) {
            $schema = $locator->findDeclarations();
            $this->memory->saveData(self::MEMORY_SECTION, $schema);
        }

        $this->configureRoutes($schema);

        foreach ($this->groups as $group) {
            $group->flushRoutes();
        }
    }

    /**
     * @return GroupRegistry
     */
    public function getGroups(): GroupRegistry
    {
        return $this->groups;
    }

    /**
     * @param array $routes
     */
    private function configureRoutes(array $routes): void
    {
        foreach ($routes as $name => $schema) {
            $this->groups
                ->getGroup($schema['group'])
                ->registerRoute(
                    $name,
                    $schema['pattern'],
                    $schema['controller'],
                    $schema['action'],
                    $schema['verbs'],
                    $schema['defaults'],
                    $schema['middleware']
                );
        }
    }
}
