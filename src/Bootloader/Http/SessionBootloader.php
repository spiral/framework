<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Http;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Session\Handler\FileHandler;
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
     * @param DirectoriesInterface  $directories
     *
     * @throws \Spiral\Core\Exception\ConfiguratorException
     */
    public function boot(ConfiguratorInterface $configurator, DirectoriesInterface $directories)
    {
        $configurator->setDefaults('session', [
            'lifetime' => 86400,
            'cookie'   => 'session',
            'secure'   => false,
            'handler'  => bind(FileHandler::class, [
                    'directory' => $directories->get('runtime') . '/session/',
                    'lifetime'  => 86400
                ]
            )
        ]);

        $session = $configurator->getConfig('session');

        $configurator->modify('http', new AppendPatch(
            'cookies.excluded',
            null,
            $session['cookie']
        ));

        $configurator->modify('http', new AppendPatch(
            'middleware',
            null,
            SessionMiddleware::class
        ));
    }
}