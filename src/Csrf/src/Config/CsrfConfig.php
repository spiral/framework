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
        'path' => '/',
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

    public function getCookiePath(): string
    {
        // Normalize null/empty to "/": an empty path makes Cookie::createHeader() omit the
        // Path= attribute, which reintroduces the fragmented-cookie bug this option prevents.
        return ($this->config['path'] ?? '') ?: '/';
    }
}
