<?php

declare(strict_types=1);

namespace Spiral\Translator\Views;

use Spiral\Translator\TranslatorInterface;
use Spiral\Views\DependencyInterface;

/**
 * Creates view cache dependency on translation locale.
 */
final class LocaleDependency implements DependencyInterface
{
    public const NAME = 'locale';

    private array $locales = [];

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
        $this->locales = $translator->getCatalogueManager()->getLocales();
    }

    public function __debugInfo(): array
    {
        return [
            'value'    => $this->getValue(),
            'variants' => $this->getVariants(),
        ];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getValue(): string
    {
        return $this->translator->getLocale();
    }

    public function getVariants(): array
    {
        return $this->locales;
    }
}
