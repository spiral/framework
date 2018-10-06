<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views;

use Spiral\Translator\CataloguesInterface;
use Spiral\Translator\TranslatorInterface;

/**
 * Creates view cache dependency on translation locale.
 */
class LocaleDependency implements DependencyInterface
{
    public const NAME = 'locale';

    /** @var TranslatorInterface */
    private $translator;

    /** @var array */
    private $locales = [];

    /**
     * @param TranslatorInterface $translator
     * @param CataloguesInterface $catalogues
     */
    public function __construct(TranslatorInterface $translator, CataloguesInterface $catalogues)
    {
        $this->translator = $translator;
        $this->locales = $catalogues->getLocales();
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
}