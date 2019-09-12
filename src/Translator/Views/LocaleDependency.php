<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
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

    /** @var TranslatorInterface */
    private $translator;

    /** @var array */
    private $locales = [];

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->locales = $translator->getCatalogueManager()->getLocales();
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->translator->getLocale();
    }

    /**
     * @inheritdoc
     */
    public function getVariants(): array
    {
        return $this->locales;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'value'    => $this->getValue(),
            'variants' => $this->getVariants()
        ];
    }
}
