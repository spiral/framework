<?php

declare(strict_types=1);

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Translator\Catalogue;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\Catalogue\RuntimeLoader;
use Spiral\Translator\CatalogueManagerInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;

class AutoRegisterTest extends TestCase
{
    public function testRegister(): void
    {
        $tr = $this->translator();

        self::assertTrue($tr->getCatalogueManager()->get('en')->has('messages', 'Welcome, {name}!'));
        self::assertFalse($tr->getCatalogueManager()->get('en')->has('messages', 'new'));

        $tr->trans('new');
        self::assertTrue($tr->getCatalogueManager()->get('en')->has('messages', 'new'));
    }

    protected function translator(): Translator
    {
        $container = new Container();
        $container->bind(TranslatorConfig::class, new TranslatorConfig([
            'locale'       => 'en',
            'autoRegister' => true,
            'domains'      => [
                'messages' => ['*']
            ]
        ]));

        $container->bindSingleton(TranslatorInterface::class, Translator::class);
        $container->bindSingleton(CatalogueManagerInterface::class, Catalogue\CatalogueManager::class);
        $container->bind(LoaderInterface::class, Catalogue\CatalogueLoader::class);

        $loader = new RuntimeLoader();
        $loader->addCatalogue('en', new Catalogue('en', [
            'messages' => [
                'Welcome, {name}!' => 'Welcome, {name}!',
                'Bye, {1}!'        => 'Bye, {1}!'
            ]
        ]));

        $container->bind(LoaderInterface::class, $loader);

        return $container->get(TranslatorInterface::class);
    }
}
