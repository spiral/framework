<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Translator\Catalogue\CatalogueLoader;
use Spiral\Translator\Catalogue\CatalogueManager;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\CatalogueManagerInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Exception\LocaleException;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;

class TranslatorTest extends TestCase
{
    public function testIsMessage(): void
    {
        $this->assertTrue(Translator::isMessage('[[hello]]'));
        $this->assertFalse(Translator::isMessage('hello'));
    }

    public function testLocale(): void
    {
        $translator = $this->translator();
        $this->assertSame('en', $translator->getLocale());

        $translator->setLocale('ru');
        $this->assertSame('ru', $translator->getLocale());
    }

    public function testLocaleException(): void
    {
        $this->expectException(LocaleException::class);

        $translator = $this->translator();
        $translator->setLocale('de');
    }

    public function testDomains(): void
    {
        $translator = $this->translator();

        $this->assertSame('spiral', $translator->getDomain('spiral-views'));
        $this->assertSame('messages', $translator->getDomain('vendor-views'));
    }

    public function testCatalogues(): void
    {
        $translator = $this->translator();
        $this->assertCount(2, $translator->getCatalogueManager()->getLocales());
    }

    public function testTrans(): void
    {
        $translator = $this->translator();
        $this->assertSame('message', $translator->trans('message'));

        $translator->setLocale('ru');
        $this->assertSame('translation', $translator->trans('message'));
    }

    protected function translator(): Translator
    {
        $container = new Container();
        $container->bind(TranslatorConfig::class, new TranslatorConfig([
            'locale'    => 'en',
            'directory' => __DIR__ . '/fixtures/locales/',
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
            'domains'   => [
                'spiral'   => [
                    'spiral-*',
                ],
                'messages' => ['*'],
            ],
        ]));

        $container->bindSingleton(TranslatorInterface::class, Translator::class);
        $container->bindSingleton(CatalogueManagerInterface::class, CatalogueManager::class);
        $container->bind(LoaderInterface::class, CatalogueLoader::class);

        return $container->get(TranslatorInterface::class);
    }
}
