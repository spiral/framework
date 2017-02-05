<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Spiral\Core\DotenvEnvironment;
use Spiral\Core\EnvironmentInterface;
use Spiral\Core\NullMemory;
use Spiral\Tests\BaseTest;

class EnvironmentTest extends BaseTest
{
    public function testInstance()
    {
        $this->assertInstanceOf(EnvironmentInterface::class, $this->app->getEnvironment());
    }

    public function testEmptyMapping()
    {
        $environment = new DotenvEnvironment(
            __DIR__ . '/Fixtures/.env',
            new NullMemory()
        );

        $this->assertSame('', $environment->get('TEST_EMPTY_1'));
        $this->assertSame('', $environment->get('TEST_EMPTY_2'));

        $this->assertNull($environment->get('TEST_EMPTY_3'));
        $this->assertNull($environment->get('TEST_EMPTY_4'));
    }

    public function testBooleanMapping()
    {
        $environment = new DotenvEnvironment(
            __DIR__ . '/Fixtures/.env',
            new NullMemory()
        );

        $this->assertTrue($environment->get('TEST_BOOLEAN_1'));
        $this->assertTrue($environment->get('TEST_BOOLEAN_2'));
        $this->assertTrue($environment->get('TEST_BOOLEAN_3'));
        $this->assertFalse($environment->get('TEST_BOOLEAN_4'));
        $this->assertFalse($environment->get('TEST_BOOLEAN_5'));
        $this->assertFalse($environment->get('TEST_BOOLEAN_6'));
    }

    public function testLoadFromMemory()
    {
        $environment = new DotenvEnvironment(__DIR__ . '/Fixtures/.env', $this->memory);

        $this->assertNotEmpty(
            $this->memory->loadData(DotenvEnvironment::MEMORY . '.' . $environment->getID())
        );

        //Re-init
        $environment = new DotenvEnvironment(__DIR__ . '/Fixtures/.env', $this->memory);

        $this->assertSame('', $environment->get('TEST_EMPTY_1'));
        $this->assertSame('', $environment->get('TEST_EMPTY_2'));

        $this->assertNull($environment->get('TEST_EMPTY_3'));
        $this->assertNull($environment->get('TEST_EMPTY_4'));

        $this->assertTrue($environment->get('TEST_BOOLEAN_1'));
        $this->assertTrue($environment->get('TEST_BOOLEAN_2'));
        $this->assertTrue($environment->get('TEST_BOOLEAN_3'));
        $this->assertFalse($environment->get('TEST_BOOLEAN_4'));
        $this->assertFalse($environment->get('TEST_BOOLEAN_5'));
        $this->assertFalse($environment->get('TEST_BOOLEAN_6'));
    }

    public function testDifferentIDs()
    {
        $environment = new DotenvEnvironment(__DIR__ . '/Fixtures/.env', $this->memory);
        $this->assertNotSame($this->app->getEnvironment()->getID(), $environment->getID());
    }

    public function testSetAndGet()
    {
        unset($_ENV['TEST']);

        $this->assertArrayNotHasKey('TEST', $_ENV);
        $this->app->getEnvironment()->set('TEST', 'abc');
        $this->assertArrayHasKey('TEST', $_ENV);

        $this->assertSame('abc', $this->app->getEnvironment()->get('TEST'));

        $this->assertSame('default', $this->app->getEnvironment()->get('other', 'default'));
        $this->assertSame('default', env('other', 'default'));

        $this->assertSame('abc', getenv('TEST'));
        $this->assertSame('abc', env('TEST'));
    }

    public function testSetEnvButBreakENV()
    {
        unset($_ENV['TEST']);

        $this->assertArrayNotHasKey('TEST', $_ENV);
        $this->app->getEnvironment()->set('TEST', 'abc');
        $this->assertArrayHasKey('TEST', $_ENV);

        unset($_ENV['TEST']);
        putenv('TEST=');

        $this->assertSame('abc', $this->app->getEnvironment()->get('TEST'));
        $this->assertSame('abc', env('TEST'));
    }
}