<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Commands;

use Spiral\Console\Console;
use Spiral\Tests\Prototype\Fixtures\TestApp;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class UsageCommandTest extends AbstractCommandsTestCase
{
    public function testList(): void
    {
        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('list', $inp, $out);

        $result = $out->fetch();

        $this->assertStringContainsString('prototype:usage', $result);
        $this->assertStringContainsString('prototype:inject', $result);
    }

    public function testPrototypes(): void
    {
        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:usage', $inp, $out);

        $result = $out->fetch();

        $this->assertStringContainsString('testClass', $result);
        $this->assertStringContainsString('undefined', $result);
    }

    public function testPrototypesBound(): void
    {
        $this->app->bindApp();

        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:usage', $inp, $out);

        $result = $out->fetch();

        $this->assertStringContainsString('testClass', $result);
        $this->assertStringNotContainsString('undefined', $result);
        $this->assertStringNotContainsString('Undefined class', $result);
        $this->assertStringContainsString(TestApp::class, $result);
    }

    public function testPrototypesBoundWithoutResolve(): void
    {
        $this->app->bindWithoutResolver();

        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:usage', $inp, $out);

        $result = $out->fetch();

        $this->assertStringContainsString('testClass', $result);
        $this->assertStringContainsString('Can\'t resolve', $result);
        $this->assertStringContainsString(TestApp::class, $result);
    }
}
