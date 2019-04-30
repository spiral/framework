<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Views;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Views\Engine\Native\NativeEngine;
use Spiral\Views\ViewManager;
use Spiral\Views\ViewsInterface;

final class ViewsBootloader extends Bootloader implements SingletonInterface
{
    const SINGLETONS = [
        ViewsInterface::class => ViewManager::class,
    ];

    /**
     * @param ConfiguratorInterface $configurator
     * @param EnvironmentInterface  $environment
     * @param DirectoriesInterface  $directories
     */
    public function boot(
        ConfiguratorInterface $configurator,
        EnvironmentInterface $environment,
        DirectoriesInterface $directories
    ) {
        if (!$directories->has('views')) {
            $directories->set('views', $directories->get('app') . 'views');
        }

        // default view config
        $configurator->setDefaults('views', [
            'cache'        => [
                'enabled'   => !$environment->get('DEBUG', false),
                'directory' => $directories->get('cache') . 'views'
            ],
            'namespaces'   => ['default' => [$directories->get('views')]],
            'dependencies' => [],
            'engines'      => [NativeEngine::class]
        ]);
    }
}