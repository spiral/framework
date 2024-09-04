<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Tests\Boot\Fixtures\TestCore;

class EnvironmentTest extends TestCase
{
    public function testValue(): void
    {
        $env = $this->getEnv(['key' => 'value']);

        $this->assertSame('value', $env->get('key'));
    }

    public function testDefault(): void
    {
        $env = $this->getEnv(['key' => 'value']);

        $this->assertSame('default', $env->get('other', 'default'));
    }

    public function testID(): void
    {
        $env = $this->getEnv(['key' => 'value']);

        $id = $env->getID();

        $this->assertNotEmpty($id);

        $env->set('other', 'value');
        $this->assertNotSame($id, $env->getID());

        $this->assertSame('value', $env->get('other', 'default'));
    }

    public function testNormalize(): void
    {
        $env = $this->getEnv(['key' => 'true', 'other' => false]);

        $this->assertTrue($env->get('key'));
        $this->assertFalse($env->get('other'));
    }

    public function testSetVariableWithOverwriting(): void
    {
        $env = $this->getEnv(['key' => 'foo']);

        $this->assertSame('foo', $env->get('key'));
        $env->set('key', 'bar');
        $this->assertSame('bar', $env->get('key'));
    }

    public function testSetVariableWithoutOverwriting(): void
    {
        $env = $this->getEnv(['key' => 'foo'], false);

        $this->assertSame('foo', $env->get('key'));
        $env->set('key', 'bar');
        $this->assertSame('foo', $env->get('key'));
    }

    public function testSetNullValueWithOverwriting(): void
    {
        $env = $this->getEnv(['key' => null]);

        $this->assertNull($env->get('key'));
        $env->set('key', 'bar');
        $this->assertSame('bar', $env->get('key'));
    }

    public function testSetNullValueWithoutOverwriting(): void
    {
        $env = $this->getEnv(['key' => null], false);

        $this->assertNull($env->get('key'));
        $env->set('key', 'bar');
        $this->assertNull($env->get('key'));
    }

    /**
     * @throws \Throwable
     */
    protected function getEnv(array $env, bool $overwite= true): EnvironmentInterface
    {
        $core = TestCore::create(['root' => __DIR__])->run(new Environment($env, $overwite));

        return $core->getContainer()->get(EnvironmentInterface::class);
    }
}
