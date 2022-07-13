<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Session\Config\SessionConfig;
use Spiral\Session\Handler\FileHandler;
use Spiral\Session\SessionFactory;
use Spiral\Session\SessionFactoryInterface;

final class SessionBootloader extends Bootloader
{
    protected const SINGLETONS = [
        SessionFactoryInterface::class => SessionFactory::class,
    ];

    /**
     * Automatically registers session starter middleware and excludes session cookie from
     * cookie protection.
     */
    public function init(
        ConfiguratorInterface $config,
        DirectoriesInterface $directories
    ): void {
        $config->setDefaults(
            SessionConfig::CONFIG,
            [
                'lifetime' => 86400,
                'cookie' => 'sid',
                'secure' => true,
                'sameSite' => null,
                'handler' => new Autowire(
                    FileHandler::class,
                    [
                        'directory' => $directories->get('runtime') . 'session',
                        'lifetime' => 86400,
                    ]
                ),
            ]
        );
    }

    public function boot(
        ConfiguratorInterface $config,
        CookiesBootloader $cookies
    ): void
    {
        $session = $config->getConfig(SessionConfig::CONFIG);

        $cookies->whitelistCookie($session['cookie']);
    }
}
