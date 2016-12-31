<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Translator;

use Mockery as m;
use Spiral\Files\FileManager;
use Spiral\Files\FilesInterface;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Loaders\PhpFileLoader;
use Spiral\Translator\LocatorInterface;
use Spiral\Translator\TranslationLocator;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\MessageCatalogue;

class LocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testSource()
    {
        $config = m::mock(TranslatorConfig::class);
        $files = m::mock(FilesInterface::class);
        $source = new TranslationLocator($config, $files);

        $this->assertInstanceOf(LocatorInterface::class, $source);
    }

    public function testHasLocale()
    {
        $config = m::mock(TranslatorConfig::class);
        $files = m::mock(FilesInterface::class);
        $source = new TranslationLocator($config, $files);

        $config->shouldReceive('localeDirectory')->with('ru')->andReturn('locales/ru');
        $files->shouldReceive('isDirectory')->with('locales/ru')->andReturn(true);

        $this->assertTrue($source->hasLocale('ru'));
        $this->assertTrue($source->hasLocale('RU'));
    }

    public function testGetLocales()
    {
        $config = m::mock(TranslatorConfig::class);
        $source = new TranslationLocator($config, new FileManager());

        $config->shouldReceive('localesDirectory')->andReturn(__DIR__ . '/fixtures/locales/');

        $this->assertSame(['en', 'ru'], $source->getLocales());
    }

    public function testLoadLocale()
    {
        $config = m::mock(TranslatorConfig::class);

        $source = new TranslationLocator($config, new FileManager());

        $config->shouldReceive('localeDirectory')->with('ru')->andReturn(
            __DIR__ . '/fixtures/locales/ru'
        );

        $config->shouldReceive('hasLoader')->with('php')->andReturn('true');
        $config->shouldReceive('hasLoader')->with('po')->andReturn('true');

        $config->shouldReceive('loaderClass')->with('php')->andReturn(PhpFileLoader::class);
        $config->shouldReceive('loaderClass')->with('po')->andReturn(PoFileLoader::class);

        $domains = $source->loadLocale('ru');
        $this->assertInternalType('array', $domains);
        $this->assertCount(2, $domains);

        $this->assertArrayHasKey('messages', $domains);
        $this->assertArrayHasKey('views', $domains);

        /**
         * @var MessageCatalogue $messages
         */
        $messages = $domains['messages'];
        $this->assertInstanceOf(MessageCatalogue::class, $messages);
        $this->assertSame(['messages'], $messages->getDomains());

        $this->assertTrue($messages->has('message'));
        $this->assertSame('translation', $messages->get('message'));

        /**
         * @var MessageCatalogue $views
         */
        $views = $domains['views'];
        $this->assertInstanceOf(MessageCatalogue::class, $views);
        $this->assertSame(['views'], $views->getDomains());

        $this->assertTrue($views->has('Welcome To Spiral', 'views'));
        $this->assertSame(
            'Добро пожаловать в Spiral Framework',
            $views->get('Welcome To Spiral', 'views')
        );

        $this->assertTrue($views->has('Twig Version', 'views'));
        $this->assertSame(
            'Twig версия',
            $views->get('Twig Version', 'views')
        );
    }
}