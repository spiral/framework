<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Session;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container\Autowire;
use Spiral\Session\Config\SessionConfig;
use Spiral\Session\Handler\FileHandler;

class ConfigTest extends TestCase
{
    public function testConfig(): void
    {
        $c = new SessionConfig([
            'lifetime' => 86400,
            'cookie'   => 'SID',
            'secure'   => false,
            'handler'  => 'files',
            'handlers' => [
                'files' => [
                    'class'   => FileHandler::class,
                    'options' => ['directory' => sys_get_temp_dir()]
                ]
            ]
        ]);

        $this->assertSame('SID', $c->getCookie());
        $this->assertFalse($c->isSecure());
        $this->assertSame(86400, $c->getLifetime());
        $this->assertNull($c->getSameSite());
        $this->assertEquals(new Autowire(FileHandler::class, [
            'directory' => sys_get_temp_dir()
        ]), $c->getHandler());
    }

    public function testConfigAutowired(): void
    {
        $c = new SessionConfig([
            'lifetime' => 86400,
            'cookie'   => 'SID',
            'secure'   => false,
            'handler'  => new Autowire(FileHandler::class, ['directory' => sys_get_temp_dir()]),
        ]);

        $this->assertEquals(new Autowire(FileHandler::class, [
            'directory' => sys_get_temp_dir()
        ]), $c->getHandler());
    }
}
