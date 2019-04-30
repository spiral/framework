<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views;

use Spiral\Translator\Catalogue\LoaderInterface as LocaleLoaderInterface;
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
     * @param TranslatorInterface   $translator
     * @param LocaleLoaderInterface $loader
     */
    public function __construct(TranslatorInterface $translator, LocaleLoaderInterface $loader)
    {
        $this->translator = $translator;
        $this->locales = $loader->getLocales();
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
            'value'  => $this->getValue(),
            'variants' => $this->getVariants()
        ];
    }
}