<?php

/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Commands;

use Spiral\Console\Console;
use Spiral\Prototype\PrototypeRegistry;
use Spiral\Tests\Prototype\Fixtures\TestApp;
use Spiral\Tests\Prototype\Fixtures\TestClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class InjectCommandTest extends AbstractCommandsTest
{
    public function testValid(): void
    {
        $this->app->bindApp();

        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:inject', $inp, $out);

        $result = $out->fetch();

        $this->assertStringContainsString(TestClass::class, $result);
        $this->assertStringContainsString(TestApp::class, $result);
    }

    public function testNone(): void
    {
        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:inject', $inp, $out);

        $result = $out->fetch();

        $this->assertSame('', $result);
    }

    public function testInvalid(): void
    {
        $this->app->get(PrototypeRegistry::class)->bindProperty('testClass', 'Invalid');

        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:inject', $inp, $out);

        $result = $out->fetch();

        $this->assertStringContainsString('Undefined class', $result);
        $this->assertStringContainsString('Invalid', $result);
    }
}
