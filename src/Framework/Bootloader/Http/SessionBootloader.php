<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Bootloader\Http\Exception\ContextualObjectNotFoundException;
use Spiral\Bootloader\Http\Exception\InvalidRequestScopeException;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config\Proxy;
use Spiral\Core\Container\Autowire;
use Spiral\Framework\Spiral;
use Spiral\Session\Config\SessionConfig;
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
        $this->binder->getBinder(Spiral::HttpRequest)->bind(SessionInterface::class, $this->resolveSession(...));
        $this->binder->getBinder(Spiral::Http)
            ->bind(
                SessionInterface::class,
                new Proxy(SessionInterface::class, false, $this->resolveSession(...)),
            );
        $this->binder->bind(SessionInterface::class, new Proxy(SessionInterface::class, true), );

        return [];
    }

    public function defineSingletons(): array
    {
        $http = $this->binder->getBinder(Spiral::Http);
        $http->bindSingleton(SessionFactory::class, SessionFactory::class);
        $http->bindSingleton(SessionFactoryInterface::class, SessionFactory::class);

        $this->binder->bind(SessionFactoryInterface::class, new Proxy(SessionFactoryInterface::class, true));

        return [];
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

    private function resolveSession(ContainerInterface $container): SessionInterface
    {
        try {
            /** @var ServerRequestInterface $request */
            $request = $container->get(ServerRequestInterface::class);
            return $request->getAttribute(SessionMiddleware::ATTRIBUTE) ?? throw new ContextualObjectNotFoundException(
                SessionInterface::class,
                SessionMiddleware::ATTRIBUTE,
            );
        } catch (InvalidRequestScopeException $e) {
            throw new InvalidRequestScopeException(SessionInterface::class, previous: $e);
        }
    }
}
