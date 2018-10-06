<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Translator\TranslatorInterface;
use Spiral\Views\Engine\Native\NativeEngine;
use Spiral\Views\LocaleDependency;
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
     * @param DirectoriesInterface  $directories
     * @param ContainerInterface    $container
     */
    public function boot(
        ConfiguratorInterface $configurator,
        DirectoriesInterface $directories,
        ContainerInterface $container
    ) {
        if (!$directories->has('views')) {
            $directories->set(
                'views',
                $directories->get('app') . 'views'
            );
        }

        $configurator->setDefaults('views', [
            'cache'        => [
                'enabled'   => true,
                'memory'    => false,
                'directory' => $directories->get('cache')
            ],
            'namespaces'   => [
                'default' => [$directories->get('views')]
            ],
            'engines'      => [NativeEngine::class],
            'dependencies' => []
        ]);

        // enable locale based cache dependency
        if ($container->has(TranslatorInterface::class)) {
            $configurator->modify('views', new AppendPatch(
                'dependencies',
                null,
                LocaleDependency::class
            ));
        }
    }
}