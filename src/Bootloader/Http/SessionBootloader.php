<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Http\Middleware\SessionMiddleware;
use Spiral\Session\Handler\FileHandler;
use Spiral\Session\SectionInterface;
use Spiral\Session\SessionSection;

final class SessionBootloader extends Bootloader implements DependedInterface
{
    const BINDINGS = [
        SectionInterface::class => SessionSection::class
    ];

    /**
     * Automatically registers session starter middleware and excludes session cookie from
     * cookie protection.
     *
     * @param ConfiguratorInterface $config
     * @param HttpBootloader        $http
     * @param DirectoriesInterface  $directories
     */
    public function boot(
        ConfiguratorInterface $config,
        HttpBootloader $http,
        DirectoriesInterface $directories
    ) {
        $config->setDefaults('session', [
            'lifetime' => 86400,
            'cookie'   => 'sid',
            'secure'   => false,
            'handler'  => new Autowire(FileHandler::class, [
                    'directory' => $directories->get('runtime') . 'session',
                    'lifetime'  => 86400
                ]
            )
        ]);

        $session = $config->getConfig('session');

        $http->whitelistCookie($session['cookie']);
        $http->addMiddleware(SessionMiddleware::class);
    }

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [HttpBootloader::class];
    }
}