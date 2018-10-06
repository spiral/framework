<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Twig\TwigCache;
use Spiral\Twig\TwigEngine;
use Spiral\Views\Config\ViewsConfig;

class TwigBootloader extends Bootloader
{
    const BOOT = true;

    const BINDINGS = [
        TwigEngine::class => [self::class, 'twigEngine']
    ];

    /**
     * @param ConfiguratorInterface $configurator
     */
    public function boot(ConfiguratorInterface $configurator)
    {
        $configurator->modify(
            'views',
            new AppendPatch('engines', null, TwigEngine::class)
        );
    }

    /**
     * @param ViewsConfig $config
     * @return TwigEngine
     */
    protected function twigEngine(ViewsConfig $config): TwigEngine
    {
        if ($config->cacheEnabled()) {
            return new TwigEngine(new TwigCache($config->cacheDirectory()));
        }

        // todo: processors

        return new TwigEngine();
    }
}