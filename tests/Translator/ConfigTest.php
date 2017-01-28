<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Translator;

use Spiral\Support\Patternizer;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Loaders\PhpFileLoader;
use Symfony\Component\Translation\Dumper\PoFileDumper;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultLocale()
    {
        $config = new TranslatorConfig([
            'locale' => 'ru'
        ]);

        $this->assertSame('ru', $config->defaultLocale());
    }

    public function testDefaultDomain()
    {
        $config = new TranslatorConfig([
            'locale' => 'ru'
        ]);

        $this->assertSame('messages', $config->defaultDomain());
    }

    public function testFallbackLocale()
    {
        $config = new TranslatorConfig([
            'fallbackLocale' => 'ru'
        ]);

        $this->assertSame('ru', $config->fallbackLocale());
    }

    public function testCacheLocales()
    {
        $config = new TranslatorConfig(['cacheLocales' => true]);
        $this->assertTrue($config->cacheLocales());

        $config = new TranslatorConfig(['cacheLocales' => false]);
        $this->assertFalse($config->cacheLocales());

        //Legacy
        $config = new TranslatorConfig(['autoReload' => true]);
        $this->assertFalse($config->cacheLocales());
    }

    public function testRegisterMessages()
    {
        $config = new TranslatorConfig(['autoRegister' => true]);
        $this->assertTrue($config->registerMessages());

        $config = new TranslatorConfig(['autoRegister' => false]);
        $this->assertFalse($config->registerMessages());

        //Legacy
        $config = new TranslatorConfig(['registerMessages' => true]);
        $this->assertTrue($config->registerMessages());
    }

    public function testLocalesDirectory()
    {
        $config = new TranslatorConfig([
            'localesDirectory' => 'directory/'
        ]);

        $this->assertSame('directory/', $config->localesDirectory());
    }

    public function testLocaleDirectory()
    {
        $config = new TranslatorConfig([
            'localesDirectory' => 'directory/'
        ]);

        $this->assertSame('directory/ru/', $config->localeDirectory('ru'));
    }

    public function testImmutable()
    {
        $config = new TranslatorConfig([]);

        $this->assertNotSame($config, $config->withPatternizer(new Patternizer()));
        $this->assertInstanceOf(
            TranslatorConfig::class,
            $config->withPatternizer(new Patternizer())
        );
    }

    public function testDomains()
    {
        $config = new TranslatorConfig([
            'domains' => [
                'spiral'   => [
                    'spiral-*'
                ],
                'messages' => ['*']
            ]
        ]);

        $this->assertSame('spiral', $config->resolveDomain('spiral-views'));
        $this->assertSame('messages', $config->resolveDomain('vendor-views'));
    }

    public function testDomainsFallback()
    {
        $config = new TranslatorConfig([
            'domains' => [
                'spiral'   => [
                    'spiral-*'
                ],
                'messages' => ['*']
            ]
        ]);

        $this->assertSame('external', $config->resolveDomain('external'));
    }

    public function testHasLoader()
    {
        $config = new TranslatorConfig([
            'loaders' => ['php' => PhpFileLoader::class]
        ]);

        $this->assertTrue($config->hasLoader('php'));
        $this->assertFalse($config->hasLoader('txt'));
    }

    public function testLoaderClass()
    {
        $config = new TranslatorConfig([
            'loaders' => ['php' => PhpFileLoader::class]
        ]);

        $this->assertSame(PhpFileLoader::class, $config->loaderClass('php'));
    }

    public function testHasDumper()
    {
        $config = new TranslatorConfig([
            'dumpers' => ['po' => PoFileDumper::class]
        ]);

        $this->assertTrue($config->hasDumper('po'));
        $this->assertFalse($config->hasDumper('xml'));
    }

    public function testDumperClass()
    {
        $config = new TranslatorConfig([
            'dumpers' => ['po' => PoFileDumper::class]
        ]);

        $this->assertSame(PoFileDumper::class, $config->dumperClass('po'));
    }
}