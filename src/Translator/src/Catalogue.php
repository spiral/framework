<?php

declare(strict_types=1);

namespace Spiral\Translator;

use Spiral\Translator\Exception\CatalogueException;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Similar to Symfony catalogue, however this one does not operate with fallback locale.
 * Provides ability to cache domains in memory.
 */
final class Catalogue implements CatalogueInterface
{
    /**
     * @param array<string, array<string, string>> $data
     */
    public function __construct(
        private readonly string $locale,
        private array $data = []
    ) {
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getDomains(): array
    {
        return \array_keys($this->data);
    }

    public function has(string $domain, string $id): bool
    {
        if (!isset($this->data[$domain])) {
            return false;
        }

        return \array_key_exists($id, $this->data[$domain]);
    }

    public function get(string $domain, string $id): string
    {
        if (!$this->has($domain, $id)) {
            throw new CatalogueException(\sprintf("Undefined string in domain '%s'", $domain));
        }

        return $this->data[$domain][$id];
    }

    public function set(string $domain, string $id, string $translation): void
    {
        $this->data[$domain][$id] = $translation;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param bool $follow When set to true messages from given catalogue will overwrite
     *                     existed messages.
     */
    public function mergeFrom(MessageCatalogue $catalogue, bool $follow = true): void
    {
        foreach ($catalogue->all() as $domain => $messages) {
            if (!isset($this->data[$domain])) {
                $this->data[$domain] = [];
            }

            if ($follow) {
                //MessageCatalogue string has higher priority that string stored in memory
                $this->data[$domain] = \array_merge($messages, $this->data[$domain]);
            } else {
                $this->data[$domain] = \array_merge($this->data[$domain], $messages);
            }
        }
    }

    /**
     * Converts into one MessageCatalogue.
     */
    public function toMessageCatalogue(): MessageCatalogue
    {
        return new MessageCatalogue($this->locale, $this->data);
    }
}
