<?php

/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Csrf\Config;

use Spiral\Core\InjectableConfig;

final class CsrfConfig extends InjectableConfig
{
    public const CONFIG = 'csrf';

    /**
     * @var array
     */
    protected $config = [
        'cookie'   => 'csrf-token',
        'length'   => 16,
        'lifetime' => 86400,
    ];

    public function getTokenLength(): int
    {
        return $this->config['length'];
    }

    public function getCookie(): string
    {
        return $this->config['cookie'];
    }


    public function getCookieLifetime(): ?int
    {
        return $this->config['lifetime'] ?? null;
    }

    public function isCookieSecure(): bool
    {
        return !empty($this->config['secure']);
    }

    public function getSameSite(): ?string
    {
        return $this->config['sameSite'] ?? null;
    }
}
