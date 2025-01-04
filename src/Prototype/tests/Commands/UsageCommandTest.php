<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Commands;

use Spiral\Console\Console;
use Spiral\Tests\Prototype\Fixtures\TestApp;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class UsageCommandTest extends AbstractCommandsTestCase
{
    public function testCommandRegistered(): void
    {
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('list', new ArrayInput([]), $out);

        $result = $out->fetch();

        self::assertStringContainsString('prototype:usage', $result);
    }

    public function testPrototypes(): void
    {
        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:usage', $inp, $out);

        $result = $out->fetch();

        self::assertStringContainsString('testClass', $result);
        self::assertStringContainsString('undefined', $result);
    }

    public function testPrototypesBound(): void
    {
        $this->app->bindApp();

        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:usage', $inp, $out);

        $result = $out->fetch();

        self::assertStringContainsString('testClass', $result);
        self::assertStringNotContainsString('undefined', $result);
        self::assertStringNotContainsString('Undefined class', $result);
        self::assertStringContainsString(TestApp::class, $result);
    }

    public function testPrototypesBoundWithoutResolve(): void
    {
        $this->app->bindWithoutResolver();

        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:usage', $inp, $out);

        $result = $out->fetch();

        self::assertStringContainsString('testClass', $result);
        self::assertStringContainsString('Can\'t resolve', $result);
        self::assertStringContainsString(TestApp::class, $result);
    }
}
