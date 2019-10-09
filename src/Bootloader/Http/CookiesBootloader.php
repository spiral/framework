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
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Cookies\Config\CookiesConfig;
use Spiral\Cookies\Middleware\CookiesMiddleware;
use Spiral\Core\Container\SingletonInterface;

final class CookiesBootloader extends Bootloader implements SingletonInterface
{
    protected const DEPENDENCIES = [
        HttpBootloader::class
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
    public function boot(HttpBootloader $http)
    {
        $this->config->setDefaults('cookies', [
            'domain'   => '.%s',
            'method'   => CookiesConfig::COOKIE_ENCRYPT,
            'excluded' => ['PHPSESSID', 'csrf-token']
        ]);

        $http->addMiddleware(CookiesMiddleware::class);
    }

    /**
     * Disable protection for given cookie.
     *
     * @param string $cookie
     */
    public function whitelistCookie(string $cookie)
    {
        $this->config->modify('cookies', new Append('excluded', null, $cookie));
    }
}
