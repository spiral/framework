<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Translator;

use Mockery as m;
use Spiral\Core\MemoryInterface;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\TranslationLocator;
use Spiral\Translator\Translator;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageSelector;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructAndSourceAccess()
    {
        $config = m::mock(TranslatorConfig::class);
        $config->shouldReceive('defaultLocale')->andReturn('en');
        $config->shouldReceive('defaultDomain')->andReturn('messages');

        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->with(Translator::MEMORY)->andReturn([]);

        $translator = new Translator(
            $config,
            $source = m::mock(TranslationLocator::class),
            $memory,
            $selector = m::mock(MessageSelector::class)
        );

        $this->assertSame($source, $translator->getSource());
    }

    public function testDomainResolution()
    {
        $config = m::mock(TranslatorConfig::class);
        $config->shouldReceive('defaultLocale')->andReturn('en');
        $config->shouldReceive('defaultDomain')->andReturn('messages');

        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->with(Translator::MEMORY)->andReturn([]);

        $translator = new Translator(
            $config,
            $source = m::mock(TranslationLocator::class),
            $memory,
            $selector = m::mock(MessageSelector::class)
        );

        $config->shouldReceive('resolveDomain')->with('bundle')->andReturn('domain');

        $this->assertSame('domain', $translator->resolveDomain('bundle'));
    }

    public function testGetLocalesFromCache()
    {
        $config = m::mock(TranslatorConfig::class);
        $config->shouldReceive('defaultLocale')->andReturn('en');
        $config->shouldReceive('defaultDomain')->andReturn('messages');

        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->with(Translator::MEMORY)->andReturn([
            'en' => true,
            'ru' => true,
            'by' => true
        ]);

        $source = m::mock(TranslationLocator::class);

        $translator = new Translator(
            $config,
            $source,
            $memory,
            $selector = m::mock(MessageSelector::class)
        );

        $this->assertSame(['en', 'ru', 'by'], $translator->getLocales(false));
    }

    public function testGetLocalesFromSource()
    {
        $config = m::mock(TranslatorConfig::class);
        $config->shouldReceive('defaultLocale')->andReturn('en');
        $config->shouldReceive('defaultDomain')->andReturn('messages');

        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->with(Translator::MEMORY)->andReturn([]);

        $source = m::mock(TranslationLocator::class);

        $translator = new Translator(
            $config,
            $source,
            $memory,
            $selector = m::mock(MessageSelector::class)
        );

        $source->shouldReceive('getLocales')->andReturn(['en', 'ru', 'by']);

        $this->assertSame(['en', 'ru', 'by'], $translator->getLocales(false));
    }

    /**
     * @expectedException \Spiral\Translator\Exceptions\LocaleException
     * @expectedExceptionMessage Undefined locale 'ru'
     */
    public function testUndefinedLocaleException()
    {
        $config = m::mock(TranslatorConfig::class);
        $config->shouldReceive('defaultLocale')->andReturn('en');
        $config->shouldReceive('defaultDomain')->andReturn('messages');

        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->with(Translator::MEMORY)->andReturn([]);

        $source = m::mock(TranslationLocator::class);

        $translator = new Translator(
            $config,

            $source,
            $memory,
            $selector = m::mock(MessageSelector::class)
        );

        $source->shouldReceive('hasLocale')->with('ru')->andReturn(false);
        $translator->setLocale('ru');
    }

    public function testTrans()
    {
        $config = m::mock(TranslatorConfig::class);
        $config->shouldReceive('defaultLocale')->andReturn('en');
        $config->shouldReceive('defaultDomain')->andReturn('messages');

        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->with(Translator::MEMORY)->andReturn([]);

        $source = m::mock(TranslationLocator::class);

        $translator = new Translator(
            $config,

            $source,
            $memory,
            $selector = m::mock(MessageSelector::class)
        );

        $source->shouldReceive('getLocales')->andReturn([]);
        $source->shouldReceive('hasLocale')->with('ru')->andReturn(true);

        $translator = $translator->withLocale('ru');

        $source->shouldReceive('loadLocale')->with('ru')->andReturn([
            new MessageCatalogue('ru', [
                'domain' => [
                    'Welcome {name}?' => 'Welcome {name}!'
                ]
            ])
        ]);

        $memory->shouldReceive('saveData')->with(
            'ru-domain',
            [
                'Welcome {name}?' => 'Welcome {name}!'
            ],
            Translator::MEMORY
        );

        //Locales cache
        $memory->shouldReceive('saveData')->with(
            Translator::MEMORY,
            [
                'ru' => ['domain']
            ]
        );

        $this->assertSame('Welcome Test!', $translator->trans(
            'Welcome {name}?',
            ['name' => 'Test'],
            'domain'
        ));
    }

    public function testTransChoice()
    {
        $config = m::mock(TranslatorConfig::class);
        $config->shouldReceive('defaultLocale')->andReturn('en');
        $config->shouldReceive('defaultDomain')->andReturn('messages');

        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->with(Translator::MEMORY)->andReturn([]);

        $source = m::mock(TranslationLocator::class);

        $translator = new Translator(
            $config,

            $source,
            $memory,
            $selector = m::mock(MessageSelector::class)
        );

        $source->shouldReceive('getLocales')->andReturn([]);
        $source->shouldReceive('hasLocale')->with('ru')->andReturn(true);

        $translator = $translator->withLocale('ru');

        $source->shouldReceive('loadLocale')->with('ru')->andReturn([
            new MessageCatalogue('ru', [
                'domain' => [
                    'Welcome {name}?' => 'Welcome {name}!'
                ]
            ])
        ]);

        $memory->shouldReceive('saveData')->with(
            'ru-domain',
            [
                'Welcome {name}?' => 'Welcome {name}!'
            ],
            Translator::MEMORY
        );

        //Locales cache
        $memory->shouldReceive('saveData')->with(
            Translator::MEMORY,
            [
                'ru' => ['domain']
            ]
        );

        $selector->shouldReceive('choose')->with(
            'Welcome {name}!',
            10,
            'ru'
        )->andReturn('Welcome {name}, test!');

        $this->assertSame('Welcome Test, test!', $translator->transChoice(
            'Welcome {name}?',
            10,
            ['name' => 'Test'],
            'domain'
        ));
    }

    public function testIsMessage()
    {
        $this->assertTrue(Translator::isMessage('[[hello]]'));
        $this->assertFalse(Translator::isMessage('hello'));
    }
}