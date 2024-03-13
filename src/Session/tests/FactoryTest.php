<?php

declare(strict_types=1);

namespace Spiral\Tests\Session;

use Spiral\Session\Config\SessionConfig;
use Spiral\Session\Exception\SessionException;
use Spiral\Session\Handler\FileHandler;
use Spiral\Session\Session;
use Spiral\Session\SessionFactory;
use Spiral\Session\SessionInterface;

final class FactoryTest extends TestCase
{
    public function tearDown(): void
    {
        if ((int)session_status() === PHP_SESSION_ACTIVE) {
            session_abort();
        }
    }

    public function testConstructInvalid(): void
    {
        $this->expectException(SessionException::class);
        $factory = new SessionFactory(new SessionConfig([
            'lifetime' => 86400,
            'cookie'   => 'SID',
            'secure'   => false,
            'handler'  => FileHandler::class,
            'handlers' => [
                //No directory
            ]
        ]), $this->container);

        $factory->initSession('sig', 'sessionid');
    }

    public function testAlreadyStarted(): void
    {
        $this->expectException(SessionException::class);
        $factory = new SessionFactory(new SessionConfig([
            'lifetime' => 86400,
            'cookie'   => 'SID',
            'secure'   => false,
            'handler'  => FileHandler::class,
            'handlers' => [
                //No directory
            ]
        ]), $this->container);

        $factory->initSession('sig', 'sessionid');
    }

    public function testMultipleSessions(): void
    {
        $this->expectExceptionMessage('Unable to initiate session, session already started');
        $this->expectException(SessionException::class);
        $factory = new SessionFactory(new SessionConfig([
            'lifetime' => 86400,
            'cookie'   => 'SID',
            'secure'   => false,
            'handler'  => null,
            'handlers' => []
        ]), $this->container);

        $this->container->bind(SessionInterface::class, Session::class);

        $session = $factory->initSession('sig');
        $session->resume();

        $factory->initSession('sig', $session->getID());
    }
}
