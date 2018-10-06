<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Views;

use Psr\Container\ContainerInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Translator\TranslatorInterface;
use Spiral\Views\LocaleDependency;

class TranslateBootloader extends Bootloader
{
    const BOOT = true;

    /**
     * @param ConfiguratorInterface $configurator
     * @param ContainerInterface    $container
     */
    public function boot(ConfiguratorInterface $configurator, ContainerInterface $container)
    {
        // enable locale based cache dependency
        if ($container->has(TranslatorInterface::class)) {
            $configurator->modify(
                'views',
                new AppendPatch('dependencies', null, LocaleDependency::class)
            );
        }

        // todo: enable for twig and stempler
    }
}