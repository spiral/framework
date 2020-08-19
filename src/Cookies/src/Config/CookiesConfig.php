<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Cookies\Config;

use Psr\Http\Message\UriInterface;
use Spiral\Core\InjectableConfig;

final class CookiesConfig extends InjectableConfig
{
    public const CONFIG = 'cookies';

    /**
     * Cookie protection methods.
     */
    public const COOKIE_UNPROTECTED = 0;
    public const COOKIE_ENCRYPT     = 1;
    public const COOKIE_HMAC        = 2;

    /**
     * Algorithm used to sign cookies.
     */
    public const HMAC_ALGORITHM = 'sha256';

    /**
     * Generated MAC length, has to be stripped from cookie.
     */
    public const MAC_LENGTH = 64;

    /**
     * @var array
     */
    protected $config = [
        'domain'   => '.%s',
        'method'   => self::COOKIE_ENCRYPT,
        'excluded' => ['PHPSESSID', 'csrf-token']
    ];

    /**
     * Return domain associated with the cookie.
     *
     * @param UriInterface $uri
     * @return string|null
     */
    public function resolveDomain(UriInterface $uri): ?string
    {
        $host = $uri->getHost();
        if (empty($host)) {
            return null;
        }

        $pattern = $this->config['domain'];
        if (preg_match("/^(\d{1,3}){4}:\d+$/", $host, $matches)) {
            // remove port
            $host = $matches[1];
        }

        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            //We can't use sub-domains when website required by IP
            $pattern = ltrim($pattern, '.');
        }

        if (strpos($pattern, '%s') === false) {
            //Forced domain
            return $pattern;
        }

        return sprintf($pattern, $host);
    }

    /**
     * Cookie protection method.
     *
     * @return int
     */
    public function getProtectionMethod(): int
    {
        return $this->config['method'];
    }

    /**
     * Cookies excluded from protection.
     *
     * @return array
     */
    public function getExcludedCookies(): array
    {
        return $this->config['excluded'];
    }
}
