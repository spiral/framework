<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

    /**
     * @param array $env
     * @return EnvironmentInterface
     * @throws \Throwable
     */
    protected function getEnv(array $env): EnvironmentInterface
    {
        $core = TestCore::init(['root' => __DIR__], new Environment($env));

        return $core->getContainer()->get(EnvironmentInterface::class);
    }
}
