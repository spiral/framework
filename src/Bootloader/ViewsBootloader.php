<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Boot\DirectoriesInterface;
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
     * @param DirectoriesInterface  $directories
     */
    public function boot(ConfiguratorInterface $configurator, DirectoriesInterface $directories)
    {
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
    }
}