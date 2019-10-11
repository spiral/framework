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
use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Session\Handler\FileHandler;
use Spiral\Session\Middleware\SessionMiddleware;
use Spiral\Session\SessionInterface;
use Spiral\Session\SessionScope;

//use Spiral\Session\SectionInterface;

final class SessionBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        HttpBootloader::class,
        CookiesBootloader::class
    ];

    protected const SINGLETONS = [
        SessionInterface::class => SessionScope::class
    ];

    /** @var ConfiguratorInterface */
    private $config;

    /**
     * @param ConfiguratorInterface $config
     */
    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Automatically registers session starter middleware and excludes session cookie from
     * cookie protection.
     *
     * @param ConfiguratorInterface $config
     * @param CookiesBootloader     $cookies
     * @param HttpBootloader        $http
     * @param DirectoriesInterface  $directories
     */
    public function boot(
        ConfiguratorInterface $config,
        CookiesBootloader $cookies,
        HttpBootloader $http,
        DirectoriesInterface $directories
    ): void {
        $config->setDefaults('session', [
            'lifetime' => 86400,
            'cookie'   => 'sid',
            'secure'   => false,
            'handler'  => new Autowire(
                FileHandler::class,
                [
                    'directory' => $directories->get('runtime') . 'session',
                    'lifetime'  => 86400
                ]
            )
        ]);

        $session = $config->getConfig('session');

        $http->addMiddleware(SessionMiddleware::class);
        $cookies->whitelistCookie($session['cookie']);
    }
}
