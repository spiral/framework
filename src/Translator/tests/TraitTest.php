<?php

declare(strict_types=1);

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Translator\Catalogue\CatalogueLoader;
use Spiral\Translator\Catalogue\CatalogueManager;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\CatalogueManagerInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Traits\TranslatorTrait;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;

class TraitTest extends TestCase
{
    use TranslatorTrait;

    private Container $container;

    public function testScopeException(): void
    {
        self::assertSame('message', $this->say('message'));
    }

    public function testTranslate(): void
    {
        ContainerScope::runScope($this->container, function (): void {
            self::assertSame('message', $this->say('message'));
        });


        $this->container->get(TranslatorInterface::class)->setLocale('ru');

        ContainerScope::runScope($this->container, function (): void {
            self::assertSame('translation', $this->say('message'));
        });

        ContainerScope::runScope($this->container, function (): void {
            self::assertSame('translation', $this->say('[[message]]'));
        });
    }

    protected function setUp(): void
    {
        $this->container = new Container();

        $this->container->bind(TranslatorConfig::class, new TranslatorConfig([
            'locale'    => 'en',
            'directory' => __DIR__ . '/fixtures/locales/',
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
            'domains'   => [
                'messages' => ['*'],
            ],
        ]));

        $this->container->bindSingleton(TranslatorInterface::class, Translator::class);
        $this->container->bindSingleton(CatalogueManagerInterface::class, CatalogueManager::class);
        $this->container->bind(LoaderInterface::class, CatalogueLoader::class);
    }
}
