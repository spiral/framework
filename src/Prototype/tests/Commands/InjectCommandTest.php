<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Commands;

use Spiral\Tests\Prototype\Fixtures\InheritedInjection\InjectionOne;
use Spiral\Tests\Prototype\Fixtures\InheritedInjection\InjectionTwo;
use Spiral\Tests\Prototype\Fixtures\InheritedInjection\ParentClass;
use Spiral\Tests\Prototype\Fixtures\InheritedInjection\MiddleClass;
use Spiral\Tests\Prototype\Fixtures\InheritedInjection\ChildClass;
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

        $this->assertStringContainsString('prototype:inject', $result);
    }

    public function testEmptyInjection(): void
    {
        $target = EmptyInjectionClass::class;
        $reflection = new \ReflectionClass($target);
        $filename = $reflection->getFileName();
        $source = file_get_contents($filename);
        $this->assertStringContainsString('use PrototypeTrait;', $source);

        try {
            $this->app->bindApp();

            $inp = new ArrayInput(['--remove' => true]);
            $out = new BufferedOutput();
            $this->app->get(Console::class)->run('prototype:inject', $inp, $out);

            $this->assertStringNotContainsString('use PrototypeTrait;', file_get_contents($filename));
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

        $this->assertStringContainsString('Can\'t resolve', $result);
        $this->assertStringContainsString('Invalid', $result);
    }

    public function testInheritedInjection(): void
    {
        $this->app->bindApp();

        $inp = new ArrayInput([]);
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:inject', $inp, $out);

        $result = $out->fetch();

        $this->assertStringContainsString(InjectionOne::class, $result);
        $this->assertStringContainsString(InjectionTwo::class, $result);
        $this->assertStringContainsString(ParentClass::class, $result);
        $this->assertStringContainsString(MiddleClass::class, $result);
        $this->assertStringContainsString(ChildClass::class, $result);

        $this->assertSame(['one'], $this->getParameters(ParentClass::class));
        $this->assertSame(['one', 'ownInjection'], $this->getParameters(MiddleClass::class));
        $this->assertSame(['two', 'one', 'ownInjection'], $this->getParameters(ChildClass::class));
    }

    private function getParameters(string $class): array
    {
        return array_keys((new Extractor())->extractFromFilename((new \ReflectionClass($class))->getFileName()));
    }
}
