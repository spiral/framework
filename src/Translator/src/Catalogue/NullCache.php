<?php

declare(strict_types=1);

namespace Spiral\Translator\Catalogue;

final class NullCache implements CacheInterface
{
    public function setLocales(?array $locales): void
    {
    }

    public function getLocales(): ?array
    {
        return null;
    }

    public function saveLocale(string $locale, ?array $data): void
    {
    }

    public function loadLocale(string $locale): ?array
    {
        return null;
    }
}
