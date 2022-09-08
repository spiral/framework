<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use ReflectionClass;
use ReflectionException;
use Throwable;

class CommandTest extends AbstractCommandTest
{
    /**
     * @dataProvider commandDataProvider
     * @param string      $className
     * @param string      $name
     * @param string|null $alias
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffold(string $className, string $name, ?string $alias): void
    {
        $input = [
            'name'          => $name,
            'alias'         => $alias,
            '--description' => 'My sample command description',
        ];
        if ($alias === null) {
            unset($input['alias']);
        }

        $this->console()->run('create:command', $input);

        clearstatcache();
        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);
        $content = $this->files()->read($reflection->getFileName());
        $classNameParts = \explode('\\', $className);

        $this->assertStringContainsString('strict_types=1', $content);
        $this->assertStringContainsString('{project-name}', $content);
        $this->assertStringContainsString('@author {author-name}', $content);
        $this->assertTrue($reflection->hasMethod('perform'));
        $this->assertTrue($reflection->hasConstant('NAME'));
        $this->assertTrue($reflection->hasConstant('DESCRIPTION'));
        $this->assertTrue($reflection->hasConstant('ARGUMENTS'));
        $this->assertTrue($reflection->hasConstant('OPTIONS'));
        $this->assertSame($alias ?? $name, $reflection->getConstant('NAME'));
        $this->assertSame('My sample command description', $reflection->getConstant('DESCRIPTION'));
        $this->assertSame($classNameParts[\array_key_last($classNameParts)], $reflection->getShortName());

        $this->deleteDeclaration($className);
    }

    public function commandDataProvider(): array
    {
        return [
            ['\\Spiral\\Tests\\Scaffolder\\App\\Command\\SampleCommand', 'sample', null],
            ['\\Spiral\\Tests\\Scaffolder\\App\\Command\\SomeCommand', 'SomeCommand', null],
            ['\\Spiral\\Tests\\Scaffolder\\App\\Command\\SampleAliasCommand', 'sampleAlias', 'my-sample-command-alias'],
        ];
    }
}
