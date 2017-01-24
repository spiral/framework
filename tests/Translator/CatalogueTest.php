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
use Spiral\Translator\Catalogue;
use Spiral\Translator\Translator;
use Symfony\Component\Translation\MessageCatalogue;

class CatalogueTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadLocale()
    {
        $memory = m::mock(MemoryInterface::class);
        $catalogue = new Catalogue('ru', $memory);

        $this->assertSame('ru', $catalogue->getLocale());
    }

    public function testGetEmptyDomains()
    {
        $memory = m::mock(MemoryInterface::class);
        $catalogue = new Catalogue('ru', $memory);

        $this->assertSame([], $catalogue->loadedDomains());
    }

    public function testLoadDomainsFromMemory()
    {
        $memory = m::mock(MemoryInterface::class);
        $catalogue = new Catalogue('ru', $memory);

        $memory->shouldReceive('loadData')->with(Translator::MEMORY . '.ru-messages')->andReturn([
            'message' => 'Russian Translation'
        ]);

        $memory->shouldReceive('loadData')->with(Translator::MEMORY . '.ru-views')->andReturn([
            'view' => 'Russian View'
        ]);

        $catalogue->loadDomains(['messages', 'views']);

        $this->assertSame(['messages', 'views'], $catalogue->loadedDomains());
    }

    public function testLoadAndHas()
    {
        $memory = m::mock(MemoryInterface::class);
        $catalogue = new Catalogue('ru', $memory);

        $memory->shouldReceive('loadData')->with(Translator::MEMORY . '.ru-messages')->andReturn([
            'message' => 'Russian Translation'
        ]);

        $memory->shouldReceive('loadData')->with(Translator::MEMORY . '.ru-views')->andReturn([
            'view' => 'Russian View'
        ]);

        $catalogue->loadDomains(['messages', 'views']);

        $this->assertSame(['messages', 'views'], $catalogue->loadedDomains());

        $this->assertTrue($catalogue->has('messages', 'message'));
        $this->assertTrue($catalogue->has('views', 'view'));

        $this->assertFalse($catalogue->has('messages', 'another-message'));

        $memory->shouldReceive('loadData')->with(Translator::MEMORY . '.ru-other-domain')->andReturn(
            null
        );

        $this->assertFalse($catalogue->has('other-domain', 'message'));
    }

    /**
     * @expectedException \Spiral\Translator\Exceptions\CatalogueException
     * @expectedExceptionMessage Undefined string in domain 'domain'
     */
    public function testUndefinedString()
    {
        $memory = m::mock(MemoryInterface::class);
        $catalogue = new Catalogue('ru', $memory);

        $memory->shouldReceive('loadData')->with(Translator::MEMORY . '.ru-domain')->andReturn(null);
        $catalogue->get('domain', 'message');
    }

    public function testLoadAndGet()
    {
        $memory = m::mock(MemoryInterface::class);
        $catalogue = new Catalogue('ru', $memory);

        $memory->shouldReceive('loadData')->with(Translator::MEMORY . '.ru-messages')->andReturn([
            'message' => 'Russian Translation'
        ]);

        $memory->shouldReceive('loadData')->with(Translator::MEMORY . '.ru-views')->andReturn([
            'view' => 'Russian View'
        ]);

        $catalogue->loadDomains(['messages', 'views']);

        $this->assertSame(['messages', 'views'], $catalogue->loadedDomains());

        $this->assertSame('Russian Translation', $catalogue->get('messages', 'message'));
        $this->assertSame('Russian View', $catalogue->get('views', 'view'));
    }

    public function testLoadGetAndSet()
    {
        $memory = m::mock(MemoryInterface::class);
        $catalogue = new Catalogue('ru', $memory);
        $memory->shouldReceive('loadData')->with(Translator::MEMORY . '.ru-messages')->andReturn([
            'message' => 'Russian Translation'
        ]);

        $memory->shouldReceive('loadData')->with(Translator::MEMORY . '.ru-views')->andReturn([
            'view' => 'Russian View'
        ]);

        $catalogue->loadDomains(['messages', 'views']);

        $this->assertSame(['messages', 'views'], $catalogue->loadedDomains());

        $this->assertSame('Russian Translation', $catalogue->get('messages', 'message'));
        $this->assertSame('Russian View', $catalogue->get('views', 'view'));

        $this->assertFalse($catalogue->has('views', 'message'));
        $catalogue->set('views', 'message', 'View Message');
        $this->assertTrue($catalogue->has('views', 'message'));

        $this->assertSame('View Message', $catalogue->get('views', 'message'));
    }

    public function testSaveDomains()
    {
        $memory = m::mock(MemoryInterface::class);
        $catalogue = new Catalogue('ru', $memory);

        $memory->shouldReceive('loadData')->with(Translator::MEMORY . '.ru-test')->andReturn([
            'existed' => 'Value'
        ]);

        $catalogue->loadDomains(['test']);
        $catalogue->set('test', 'message', 'Some Test Message');

        $memory->shouldReceive('saveData')->with(
            Translator::MEMORY . '.ru-test',
            [
                'existed' => 'Value',
                'message' => 'Some Test Message'
            ]
        );

        $catalogue->saveDomains();
    }

    public function testMergeSymfonyAndFollow()
    {
        $memory = m::mock(MemoryInterface::class);
        $catalogue = new Catalogue('ru', $memory);

        $catalogue->set('domain', 'message', 'Original Translation');
        $this->assertSame('Original Translation', $catalogue->get('domain', 'message'));

        $messageCatalogue = new MessageCatalogue('ru', ['domain' => ['message' => 'Translation']]);
        $catalogue->mergeFrom($messageCatalogue, true);

        $this->assertSame('Original Translation', $catalogue->get('domain', 'message'));
    }

    public function testMergeSymfonyAndReplace()
    {
        $memory = m::mock(MemoryInterface::class);
        $catalogue = new Catalogue('ru', $memory);

        $catalogue->set('domain', 'message', 'Original Translation');
        $this->assertSame('Original Translation', $catalogue->get('domain', 'message'));

        $messageCatalogue = new MessageCatalogue('ru', ['domain' => ['message' => 'Translation']]);
        $catalogue->mergeFrom($messageCatalogue, false);

        $this->assertSame('Translation', $catalogue->get('domain', 'message'));
    }
}