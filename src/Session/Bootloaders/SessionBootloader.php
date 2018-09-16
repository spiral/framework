<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Session\Bootloaders;

use Spiral\Config\ModifierInterface;
use Spiral\Config\Patches\AppendPatch;
use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Session\Configs\SessionConfig;
use Spiral\Session\Middleware\SessionMiddleware;
use Spiral\Session\SectionInterface;
use Spiral\Session\Session;
use Spiral\Session\SessionInterface;
use Spiral\Session\SessionSection;

class SessionBootloader extends Bootloader
{
    const BOOT = true;

    const BINDINGS = [
        SessionInterface::class => Session::class,
        SectionInterface::class => SessionSection::class
    ];

    /**
     * Automatically registers session starter middleware and excludes session cookie from
     * cookie protection.
     *
     * @param ConfiguratorInterface $configurator
     * @param ModifierInterface     $modifier
     */
    public function boot(ConfiguratorInterface $configurator, ModifierInterface $modifier)
    {
        $session = $configurator->getConfig('session');

        $modifier->modify('http', new AppendPatch(
            'cookies.excluded',
            null,
            $session['cookie']
        ));

        $modifier->modify('http', new AppendPatch(
            'middleware',
            null,
            SessionMiddleware::class
        ));
    }
}