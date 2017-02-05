<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Session;

use Spiral\Core\Container;
use Spiral\Session\Configs\SessionConfig;
use Spiral\Session\Handlers\FileHandler;
use Spiral\Session\Session;
use Spiral\Session\SessionFactory;
use Spiral\Session\SessionInterface;
use Spiral\Tests\BaseTest;

class FactoryTest extends BaseTest
{
    public function tearDown()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_abort();
        }

        parent::tearDown();
    }

    /**
     * @expectedException \Spiral\Session\Exceptions\SessionException
     * @expectedExceptionMessage Unable to init session handler Spiral\Session\Handlers\FileHandler
     */
    public function testConstructInvalid()
    {
        $factory = new SessionFactory(new SessionConfig([
            'lifetime' => 86400,
            'cookie'   => 'SID',
            'secure'   => false,
            'handler'  => FileHandler::class,
            'handlers' => [
                //No directory
            ]
        ]), new Container());

        $session = $factory->initSession('sig', 'sessionid');
    }

    /**
     * @expectedException \Spiral\Session\Exceptions\SessionException
     * @expectedExceptionMessage Unable to init session handler Spiral\Session\Handlers\FileHandler
     */
    public function testAlreadyStarted()
    {
        $factory = new SessionFactory(new SessionConfig([
            'lifetime' => 86400,
            'cookie'   => 'SID',
            'secure'   => false,
            'handler'  => FileHandler::class,
            'handlers' => [
                //No directory
            ]
        ]), new Container());

        $session = $factory->initSession('sig', 'sessionid');
    }

    /**
     * @expectedException \Spiral\Session\Exceptions\SessionException
     * @expectedExceptionMessage Unable to initiate session, session already started
     */
    public function testMultipleSessions()
    {
        $factory = new SessionFactory(new SessionConfig([
            'lifetime' => 86400,
            'cookie'   => 'SID',
            'secure'   => false,
            'handler'  => null,
            'handlers' => []
        ]), $c = new Container());

        $c->bind(SessionInterface::class, Session::class);

        $session = $factory->initSession('sig');
        $session->resume();

        $session = $factory->initSession('sig', $session->getID());
    }
}