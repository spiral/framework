<?php

declare(strict_types=1);

namespace Spiral\Tests\Session;

use Spiral\Core\Container;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Session\Config\SessionConfig;
use Spiral\Session\Handler\FileHandler;
use Spiral\Session\SessionSectionInterface;
use Spiral\Session\Session;
use Spiral\Session\SessionFactory;
use Spiral\Session\SessionInterface;
use Spiral\Session\SessionSection;

final class SessionTest extends TestCase
{
    private SessionFactory $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->container->bind(FilesInterface::class, Files::class);
        $this->container->bind(SessionInterface::class, Session::class);
        $this->container->bind(SessionSectionInterface::class, SessionSection::class);

        $this->factory = new SessionFactory(new SessionConfig([
            'lifetime' => 86400,
            'cookie'   => 'SID',
            'secure'   => false,
            'handler'  => new Container\Autowire(FileHandler::class, [
                'directory' => sys_get_temp_dir()
            ]),
        ]), $this->container);
    }

    public function tearDown(): void
    {
        if ((int)session_status() === PHP_SESSION_ACTIVE) {
            session_abort();
        }
    }

    public function testValueDestroy(): void
    {
        $session = $this->factory->initSession('sig');
        $session->getSection()->set('key', 'value');

        $this->assertSame('value', $session->getSection()->get('key'));

        $session->destroy();
        $session->resume();

        $this->assertNull($session->getSection()->get('key'));
    }


    public function testValueAbort(): void
    {
        $session = $this->factory->initSession('sig');
        $session->getSection()->set('key', 'value');

        $this->assertSame('value', $session->getSection()->get('key'));
        $id = $session->getID();

        $session->commit();

        $session = $this->factory->initSession('sig', $id);
        $this->assertSame('value', $session->getSection()->get('key'));
        $session->getSection()->set('key', 'value2');
        $this->assertSame('value2', $session->getSection()->get('key'));
        $session->abort();

        $session = $this->factory->initSession('sig', $id);
        $this->assertSame('value', $session->getSection()->get('key'));
        $session->destroy();
    }

    public function testValueRestart(): void
    {
        $session = $this->factory->initSession('sig');
        $session->getSection()->set('key', 'value');

        $this->assertSame('value', $session->getSection()->get('key'));
        $id = $session->getID();
        $session->commit();

        $session = $this->factory->initSession('sig', $id);
        $this->assertSame('value', $session->getSection()->get('key'));
    }

    public function testValueNewID(): void
    {
        $session = $this->factory->initSession('sig');
        $session->getSection()->set('key', 'value');

        $this->assertSame('value', $session->getSection()->get('key'));
        $id = $session->regenerateID()->getID();
        $session->commit();

        $session = $this->factory->initSession('sig', $id);
        $this->assertSame('value', $session->getSection()->get('key'));
    }

    public function testSection(): void
    {
        $session = $this->factory->initSession('sig');
        $section = $session->getSection('default');

        $this->assertSame('default', $section->getName());

        $section->set('key', 'value');
        foreach ($section as $key => $value) {
            $this->assertSame('key', $key);
            $this->assertSame('value', $value);
        }

        $this->assertSame('key', $key);
        $this->assertSame('value', $value);

        $this->assertSame('value', $section->pull('key'));
        $this->assertNull($section->pull('key'));
    }

    public function testSectionClear(): void
    {
        $session = $this->factory->initSession('sig');
        $section = $session->getSection('default');

        $section->set('key', 'value');
        $section->clear();
        $this->assertNull($section->pull('key'));
    }

    public function testSectionArrayAccess(): void
    {
        $session = $this->factory->initSession('sig');
        $section = $session->getSection('default');

        $section['key'] = 'value';
        $this->assertSame('value', $section['key']);
        $section->key = 'new value';
        $this->assertSame('new value', $section->key);
        $this->assertTrue(isset($section['key']));
        $this->assertTrue(! empty($section->key));

        $section->delete('key');
        $this->assertFalse(isset($section['key']));
        $this->assertObjectNotHasProperty('key', $section);

        $section->key = 'new value';
        unset($section->key);
        $this->assertObjectNotHasProperty('key', $section);


        $section->key = 'new value';
        unset($section['key']);
        $this->assertObjectNotHasProperty('key', $section);

        $section->new = 'another';

        $session->commit();

        $this->assertNull($section->get('key'));
        $this->assertSame('another', $section->get('new'));
    }

    public function testResumeAndID(): void
    {
        $session = $this->factory->initSession('sig');
        $session->resume();
        $id = $session->getID();

        $this->assertTrue($session->isStarted());
        $session->commit();

        $this->assertFalse($session->isStarted());
        $this->assertSame($id, $session->getID());
        $this->assertFalse($session->isStarted());

        $session->destroy();
        $this->assertSame($id, $session->getID());

        $this->assertSame($id, $session->__debugInfo()['id']);
        $session->regenerateID();
        $this->assertNotSame($id, $session->getID());
    }

    public function testResumeNewSession(): void
    {
        $session = $this->factory->initSession('sig');
        $session->resume();
        $id = $session->getID();

        $this->assertTrue($session->isStarted());
        $session->commit();

        $session = $this->factory->initSession('sig');
        $session->resume();

        $this->assertNotSame($id, $session->getID());
    }

    public function testSignatures(): void
    {
        $session = $this->factory->initSession('sig');
        $session->getSection()->set('key', 'value');
        $session->commit();

        $id = $session->getID();

        $session = $this->factory->initSession('sig', $id);
        $this->assertSame('value', $session->getSection()->get('key'));
        $this->assertSame($id, $session->getID());
        $session->commit();

        $session = $this->factory->initSession('different', $id);
        $this->assertNull($session->getSection()->get('key'));
        $this->assertNotSame($id, $session->getID());
        $session->commit();

        // must be dead
        $session = $this->factory->initSession('sig', $id);
        $this->assertNull($session->getSection()->get('key'));
        $this->assertNotSame($id, $session->getID());
        $session->commit();
    }
}
