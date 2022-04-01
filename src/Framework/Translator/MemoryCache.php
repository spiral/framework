<?php

declare(strict_types=1);

namespace Spiral\Translator;

use Spiral\Boot\MemoryInterface;
use Spiral\Translator\Catalogue\CacheInterface;

final class MemoryCache implements CacheInterface
{
    public function __construct(
        private readonly MemoryInterface $memory
    ) {
    }

    public function setLocales(?array $locales): void
    {
        $this->memory->saveData('i18n.locales', $locales);
    }

    public function getLocales(): ?array
    {
        return $this->memory->loadData('i18n.locales') ?? null;
    }

    public function saveLocale(string $locale, ?array $data): void
    {
        $this->memory->saveData(\sprintf('i18n.%s', $locale), $data);
    }

    public function loadLocale(string $locale): ?array
    {
        return $this->memory->loadData(\sprintf('i18n.%s', $locale)) ?? null;
    }
}
