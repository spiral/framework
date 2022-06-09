<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Cookies\Config\CookiesConfig;
use Spiral\Cookies\CookieQueue;
use Spiral\Cookies\Middleware\CookiesMiddleware;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\ScopeException;

final class CookiesBootloader extends Bootloader implements SingletonInterface
{
    protected const BINDINGS = [
        CookieQueue::class => [self::class, 'cookieQueue'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(HttpBootloader $http): void
    {
        $this->config->setDefaults(
            CookiesConfig::CONFIG,
            [
                'domain'   => '.%s',
                'method'   => CookiesConfig::COOKIE_ENCRYPT,
                'excluded' => ['PHPSESSID', 'csrf-token'],
            ]
        );

     //   $http->addMiddleware(CookiesMiddleware::class);
    }

    /**
     * Disable protection for given cookie.
     */
    public function whitelistCookie(string $cookie): void
    {
        $this->config->modify(CookiesConfig::CONFIG, new Append('excluded', null, $cookie));
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function cookieQueue(ServerRequestInterface $request): CookieQueue
    {
        $cookieQueue = $request->getAttribute(CookieQueue::ATTRIBUTE, null);
        if ($cookieQueue === null) {
            throw new ScopeException('Unable to resolve CookieQueue, invalid request scope');
        }

        return $cookieQueue;
    }
}
