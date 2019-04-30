<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Views;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Views\LocaleDependency;
use Spiral\Views\LocaleProcessor;

class TranslateBootloader extends Bootloader
{
    const BOOT = true;

    const SINGLETONS = [
        // Each engine expect to mount this process by itself
        LocaleProcessor::class => LocaleProcessor::class
    ];

    /**
     * @param ConfiguratorInterface $configurator
     */
    public function boot(ConfiguratorInterface $configurator)
    {
        // enable locale based cache dependency
        $configurator->modify(
            'views',
            new AppendPatch('dependencies', null, LocaleDependency::class)
        );
    }
}