<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

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

        $this->assertStringContainsString('strict_types=1', $this->files()->read($reflection->getFileName()));
        $this->assertTrue($reflection->hasMethod('perform'));
        $this->assertTrue($reflection->hasConstant('NAME'));
        $this->assertTrue($reflection->hasConstant('DESCRIPTION'));
        $this->assertTrue($reflection->hasConstant('ARGUMENTS'));
        $this->assertTrue($reflection->hasConstant('OPTIONS'));
        $this->assertSame($alias ?? $name, $reflection->getConstant('NAME'));
        $this->assertSame('My sample command description', $reflection->getConstant('DESCRIPTION'));

        $this->deleteDeclaration($className);
    }

    public function commandDataProvider(): array
    {
        return [
            ['\\Spiral\\Tests\\Scaffolder\\App\\Command\\SampleCommand', 'sample', null],
            ['\\Spiral\\Tests\\Scaffolder\\App\\Command\\SampleAliasCommand', 'sampleAlias', 'my-sample-command-alias'],
        ];
    }
}
