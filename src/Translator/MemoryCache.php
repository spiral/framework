<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Translator;

use Spiral\Boot\MemoryInterface;
use Spiral\Translator\Catalogue\CacheInterface;

final class MemoryCache implements CacheInterface
{
    /** @var MemoryInterface */
    private $memory;

    /**
     * @param MemoryInterface $memory
     */
    public function __construct(MemoryInterface $memory)
    {
        $this->memory = $memory;
    }

    /**
     * @inheritDoc
     */
    public function setLocales(?array $locales): void
    {
        $this->memory->saveData('i18n.locales', $locales);
    }

    /**
     * @inheritDoc
     */
    public function getLocales(): ?array
    {
        return $this->memory->loadData('i18n.locales') ?? null;
    }

    /**
     * @inheritDoc
     */
    public function saveLocale(string $locale, ?array $data): void
    {
        $this->memory->saveData("i18n.{$locale}", $data);
    }

    /**
     * @inheritDoc
     */
    public function loadLocale(string $locale): ?array
    {
        return $this->memory->loadData("i18n.{$locale}") ?? null;
    }
}
