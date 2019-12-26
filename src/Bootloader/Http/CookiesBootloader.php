<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    protected const DEPENDENCIES = [
        HttpBootloader::class
    ];

    protected const BINDINGS = [
        CookieQueue::class => [self::class, 'cookieQueue']
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
     * @param HttpBootloader $http
     */
    public function boot(HttpBootloader $http): void
    {
        $this->config->setDefaults(
            'cookies',
            [
                'domain'   => '.%s',
                'method'   => CookiesConfig::COOKIE_ENCRYPT,
                'excluded' => ['PHPSESSID', 'csrf-token']
            ]
        );

        $http->addMiddleware(CookiesMiddleware::class);
    }

    /**
     * Disable protection for given cookie.
     *
     * @param string $cookie
     */
    public function whitelistCookie(string $cookie): void
    {
        $this->config->modify('cookies', new Append('excluded', null, $cookie));
    }

    /**
     * @param ServerRequestInterface $request
     * @return CookieQueue
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
