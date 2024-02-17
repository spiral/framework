<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Cookies\Config\CookiesConfig;
use Spiral\Cookies\CookieQueue;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\BinderInterface;
use Spiral\Core\Exception\ScopeException;
use Spiral\Framework\Spiral;

#[Singleton]
final class CookiesBootloader extends Bootloader
{
    public function __construct(
        private readonly ConfiguratorInterface $config,
        private readonly BinderInterface $binder,
    ) {
    }

    public function defineBindings(): array
    {
        $this->binder->getBinder(Spiral::HttpRequest)->bind(CookieQueue::class, [self::class, 'cookieQueue']);

        return [];
    }

    public function init(): void
    {
        $this->config->setDefaults(
            CookiesConfig::CONFIG,
            [
                'domain' => '.%s',
                'method' => CookiesConfig::COOKIE_ENCRYPT,
                'excluded' => ['PHPSESSID', 'csrf-token'],
            ]
        );
    }

    /**
     * Disable protection for given cookie.
     */
    public function whitelistCookie(string $cookie): void
    {
        $this->config->modify(CookiesConfig::CONFIG, new Append('excluded', null, $cookie));
    }

    private function cookieQueue(ServerRequestInterface $request): CookieQueue
    {
        $cookieQueue = $request->getAttribute(CookieQueue::ATTRIBUTE, null);
        if ($cookieQueue === null) {
            throw new ScopeException('Unable to resolve CookieQueue, invalid request scope');
        }

        return $cookieQueue;
    }
}
