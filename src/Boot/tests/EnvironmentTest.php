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

        self::assertSame('value', $env->get('key'));
    }

    public function testDefault(): void
    {
        $env = $this->getEnv(['key' => 'value']);

        self::assertSame('default', $env->get('other', 'default'));
    }

    public function testID(): void
    {
        $env = $this->getEnv(['key' => 'value']);

        $id = $env->getID();

        self::assertNotEmpty($id);

        $env->set('other', 'value');
        self::assertNotSame($id, $env->getID());

        self::assertSame('value', $env->get('other', 'default'));
    }

    public function testNormalize(): void
    {
        $env = $this->getEnv(['key' => 'true', 'other' => false]);

        self::assertTrue($env->get('key'));
        self::assertFalse($env->get('other'));
    }

    public function testSetVariableWithOverwriting(): void
    {
        $env = $this->getEnv(['key' => 'foo']);

        self::assertSame('foo', $env->get('key'));
        $env->set('key', 'bar');
        self::assertSame('bar', $env->get('key'));
    }

    public function testSetVariableWithoutOverwriting(): void
    {
        $env = $this->getEnv(['key' => 'foo'], false);

        self::assertSame('foo', $env->get('key'));
        $env->set('key', 'bar');
        self::assertSame('foo', $env->get('key'));
    }

    public function testSetNullValueWithOverwriting(): void
    {
        $env = $this->getEnv(['key' => null]);

        self::assertNull($env->get('key'));
        $env->set('key', 'bar');
        self::assertSame('bar', $env->get('key'));
    }

    public function testSetNullValueWithoutOverwriting(): void
    {
        $env = $this->getEnv(['key' => null], false);

        self::assertNull($env->get('key'));
        $env->set('key', 'bar');
        self::assertNull($env->get('key'));
    }

    /**
     * @throws \Throwable
     */
    protected function getEnv(array $env, bool $overwite = true): EnvironmentInterface
    {
        $core = TestCore::create(['root' => __DIR__])->run(new Environment($env, $overwite));

        return $core->getContainer()->get(EnvironmentInterface::class);
    }
}
