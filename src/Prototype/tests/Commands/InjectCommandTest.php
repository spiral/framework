<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Commands;

use Spiral\Console\Console;
use Spiral\Prototype\PrototypeRegistry;
use Spiral\Tests\Prototype\Commands\Fixtures\EmptyInjectionClass;
use Spiral\Tests\Prototype\Fixtures\InheritedInjection;
use Spiral\Tests\Prototype\Fixtures\TestApp;
use Spiral\Tests\Prototype\Fixtures\TestClass;
use Spiral\Tests\Prototype\Traverse\Extractor;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class InjectCommandTest extends AbstractCommandsTestCase
{
    public function testCommandRegistered(): void
    {
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('list', new ArrayInput([]), $out);

        $result = $out->fetch();

        self::assertStringContainsString('prototype:inject', $result);
    }

    public function testEmptyInjection(): void
    {
        $target = EmptyInjectionClass::class;
        $reflection = new \ReflectionClass($target);
        $filename = $reflection->getFileName();
        $source = file_get_contents($filename);
        self::assertStringContainsString('use PrototypeTrait;', $source);

        try {
            $this->app->bindApp();

            $inp = new ArrayInput(['--remove' => true]);
            $out = new BufferedOutput();
            $this->app->get(Console::class)->run('prototype:inject', $inp, $out);

            self::assertStringNotContainsString('use PrototypeTrait;', file_get_contents($filename));
        } finally {
            file_put_contents($filename, $source);
        }
    }

    public function testValid(): void
    {
        $this->app->bindApp();

        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:inject', $inp, $out);

        $result = $out->fetch();

        self::assertStringContainsString(TestClass::class, $result);
        self::assertStringContainsString(TestApp::class, $result);
    }

    public function testNone(): void
    {
        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:inject', $inp, $out);

        $result = $out->fetch();

        self::assertSame('', $result);
    }

    public function testInvalid(): void
    {
        $this->app->get(PrototypeRegistry::class)->bindProperty('testClass', 'Invalid');

        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:inject', $inp, $out);

        $result = $out->fetch();

        self::assertStringContainsString('Can\'t autowire', $result);
        self::assertStringContainsString('Invalid', $result);
    }

    public function testInheritedInjection(): void
    {
        $this->app->bindApp();

        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:inject', $inp, $out);

        $result = $out->fetch();

        self::assertStringContainsString(InheritedInjection\InjectionOne::class, $result);
        self::assertStringContainsString(InheritedInjection\InjectionTwo::class, $result);
        self::assertStringContainsString(InheritedInjection\ParentClass::class, $result);
        self::assertStringContainsString(InheritedInjection\MiddleClass::class, $result);
        self::assertStringContainsString(InheritedInjection\ChildClass::class, $result);

        self::assertSame(['one'], $this->getParameters(InheritedInjection\ParentClass::class));
        self::assertSame(['one', 'ownInjection'], $this->getParameters(InheritedInjection\MiddleClass::class));
        self::assertSame(['two', 'one', 'ownInjection'], $this->getParameters(InheritedInjection\ChildClass::class));
    }

    private function getParameters(string $class): array
    {
        return array_keys((new Extractor())->extractFromFilename((new \ReflectionClass($class))->getFileName()));
    }
}
