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

    public function testValueDestroy(): void
    {
        $session = $this->factory->initSession('sig');
        $session->getSection()->set('key', 'value');

        self::assertSame('value', $session->getSection()->get('key'));

        $session->destroy();
        $session->resume();

        self::assertNull($session->getSection()->get('key'));
    }

    public function testValueAbort(): void
    {
        $session = $this->factory->initSession('sig');
        $session->getSection()->set('key', 'value');

        self::assertSame('value', $session->getSection()->get('key'));
        $id = $session->getID();

        $session->commit();

        $session = $this->factory->initSession('sig', $id);
        self::assertSame('value', $session->getSection()->get('key'));
        $session->getSection()->set('key', 'value2');
        self::assertSame('value2', $session->getSection()->get('key'));
        $session->abort();

        $session = $this->factory->initSession('sig', $id);
        self::assertSame('value', $session->getSection()->get('key'));
        $session->destroy();
    }

    public function testValueRestart(): void
    {
        $session = $this->factory->initSession('sig');
        $session->getSection()->set('key', 'value');

        self::assertSame('value', $session->getSection()->get('key'));
        $id = $session->getID();
        $session->commit();

        $session = $this->factory->initSession('sig', $id);
        self::assertSame('value', $session->getSection()->get('key'));
    }

    public function testValueNewID(): void
    {
        $session = $this->factory->initSession('sig');
        $session->getSection()->set('key', 'value');

        self::assertSame('value', $session->getSection()->get('key'));
        $id = $session->regenerateID()->getID();
        $session->commit();

        $session = $this->factory->initSession('sig', $id);
        self::assertSame('value', $session->getSection()->get('key'));
    }

    public function testSection(): void
    {
        $session = $this->factory->initSession('sig');
        $section = $session->getSection('default');

        self::assertSame('default', $section->getName());

        $section->set('key', 'value');
        foreach ($section as $key => $value) {
            self::assertSame('key', $key);
            self::assertSame('value', $value);
        }

        self::assertSame('key', $key);
        self::assertSame('value', $value);

        self::assertSame('value', $section->pull('key'));
        self::assertNull($section->pull('key'));
    }

    public function testSectionClear(): void
    {
        $session = $this->factory->initSession('sig');
        $section = $session->getSection('default');

        $section->set('key', 'value');
        $section->clear();
        self::assertNull($section->pull('key'));
    }

    public function testSectionArrayAccess(): void
    {
        $session = $this->factory->initSession('sig');
        $section = $session->getSection('default');

        $section['key'] = 'value';
        self::assertSame('value', $section['key']);
        $section->key = 'new value';
        self::assertSame('new value', $section->key);
        self::assertArrayHasKey('key', $section);
        self::assertTrue(isset($section->key));

        $section->delete('key');
        self::assertArrayNotHasKey('key', $section);
        self::assertFalse(isset($section->key));

        $section->key = 'new value';
        unset($section->key);
        self::assertFalse(isset($section->key));


        $section->key = 'new value';
        unset($section['key']);
        self::assertFalse(isset($section->key));

        $section->new = 'another';

        $session->commit();

        self::assertNull($section->get('key'));
        self::assertSame('another', $section->get('new'));
    }

    public function testResumeAndID(): void
    {
        $session = $this->factory->initSession('sig');
        $session->resume();
        $id = $session->getID();

        self::assertTrue($session->isStarted());
        $session->commit();

        self::assertFalse($session->isStarted());
        self::assertSame($id, $session->getID());
        self::assertFalse($session->isStarted());

        $session->destroy();
        self::assertSame($id, $session->getID());

        self::assertSame($id, $session->__debugInfo()['id']);
        $session->regenerateID();
        self::assertNotSame($id, $session->getID());
    }

    public function testResumeNewSession(): void
    {
        $session = $this->factory->initSession('sig');
        $session->resume();
        $id = $session->getID();

        self::assertTrue($session->isStarted());
        $session->commit();

        $session = $this->factory->initSession('sig');
        $session->resume();

        self::assertNotSame($id, $session->getID());
    }

    public function testSignatures(): void
    {
        $session = $this->factory->initSession('sig');
        $session->getSection()->set('key', 'value');
        $session->commit();

        $id = $session->getID();

        $session = $this->factory->initSession('sig', $id);
        self::assertSame('value', $session->getSection()->get('key'));
        self::assertSame($id, $session->getID());
        $session->commit();

        $session = $this->factory->initSession('different', $id);
        self::assertNull($session->getSection()->get('key'));
        self::assertNotSame($id, $session->getID());
        $session->commit();

        // must be dead
        $session = $this->factory->initSession('sig', $id);
        self::assertNull($session->getSection()->get('key'));
        self::assertNotSame($id, $session->getID());
        $session->commit();
    }

    protected function setUp(): void
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
                'directory' => \sys_get_temp_dir(),
            ]),
        ]), $this->container);
    }

    protected function tearDown(): void
    {
        if ((int) \session_status() === PHP_SESSION_ACTIVE) {
            \session_abort();
        }
    }
}
