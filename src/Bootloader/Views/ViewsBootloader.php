<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Views;

use Psr\Container\ContainerInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Views\Engine\Native\NativeEngine;
use Spiral\Views\ViewManager;
use Spiral\Views\ViewsInterface;

class ViewsBootloader extends Bootloader
{
    const BOOT = true;

    const SINGLETONS = [
        ViewsInterface::class => ViewManager::class,
    ];

    /**
     * @param ConfiguratorInterface $configurator
     * @param EnvironmentInterface  $environment
     * @param DirectoriesInterface  $directories
     * @param ContainerInterface    $container
     */
    public function boot(
        ConfiguratorInterface $configurator,
        EnvironmentInterface $environment,
        DirectoriesInterface $directories,
        ContainerInterface $container
    ) {
        if (!$directories->has('views')) {
            $directories->set('views', $directories->get('app') . 'views');
        }

        // default view config
        $configurator->setDefaults('views', [
            'cache'        => [
                'enabled'   => !$environment->get('DEBUG', false),
                'memory'    => !$environment->get('DEBUG', false),
                'directory' => $directories->get('cache') . 'views'
            ],
            'namespaces'   => ['default' => [$directories->get('views')]],
            'dependencies' => [],
            'engines'      => [NativeEngine::class]
        ]);

//        if ($container->has(TranslatorInterface::class)) {
//            // enable locale based cache dependency
//            $configurator->modify(
//                'views',
//                new AppendPatch('dependencies', null, LocaleDependency::class)
//            );
//        }
    }
}