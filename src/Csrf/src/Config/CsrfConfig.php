<?php

declare(strict_types=1);

namespace Spiral\Csrf\Config;

use Spiral\Core\InjectableConfig;

final class CsrfConfig extends InjectableConfig
{
    public const CONFIG = 'csrf';

    protected array $config = [
        'cookie' => 'csrf-token',
        'length' => 16,
        'lifetime' => null,
        'sameSite' => null,
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
        return $this->config['lifetime'];
    }

    public function isCookieSecure(): bool
    {
        return !empty($this->config['secure']);
    }

    public function getSameSite(): ?string
    {
        return $this->config['sameSite'];
    }
}
