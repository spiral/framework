<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config\Proxy;
use Spiral\Core\Container\Autowire;
use Spiral\Framework\Spiral;
use Spiral\Http\CurrentRequest;
use Spiral\Session\Config\SessionConfig;
use Spiral\Session\Exception\InvalidSessionContext;
use Spiral\Session\Handler\FileHandler;
use Spiral\Session\Middleware\SessionMiddleware;
use Spiral\Session\SessionFactory;
use Spiral\Session\SessionFactoryInterface;
use Spiral\Session\SessionInterface;

final class SessionBootloader extends Bootloader
{
    public function __construct(
        private readonly BinderInterface $binder,
    ) {
    }

    public function defineBindings(): array
    {
        $this->binder
            ->getBinder(Spiral::Http)
            ->bind(
                SessionInterface::class,
                static function (CurrentRequest $request): SessionInterface {
                return $request->get()
                    ->getAttribute(SessionMiddleware::ATTRIBUTE) ?? throw new InvalidSessionContext();
            }
            );
        $this->binder->bind(SessionInterface::class, new Proxy(SessionInterface::class, false));

        return [];
    }

    public function defineSingletons(): array
    {
        return [
            SessionFactoryInterface::class => SessionFactory::class,
        ];
    }

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
    ): void {
        $session = $config->getConfig(SessionConfig::CONFIG);

        $cookies->whitelistCookie($session['cookie']);
    }
}
