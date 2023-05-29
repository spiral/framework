<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;

final class CommandTest extends AbstractCommandTestCase
{
    #[DataProvider('commandDataProvider')]
    public function testScaffold(string $className, string $name, ?string $alias, $commandName): void
    {
        $this->className = $className;

        $input = [
            'name' => $name,
            'alias' => $alias,
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

        $attributes = $reflection->getAttributes();

        /** @var AsCommand $definition */
        $definition = $attributes[0]->newInstance();

        $this->assertStringContainsString('strict_types=1', $content);
        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->hasMethod('__invoke'));
        $this->assertEquals($commandName, $definition->name);
        $this->assertEquals('My sample command description', $definition->description);
        $this->assertSame($classNameParts[\array_key_last($classNameParts)], $reflection->getShortName());
    }

    public function testAddArgument(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Command\\ArgumentCommand';

        $this->console()->run('create:command', [
            'name' => 'Argument',
            '--argument' => ['username', 'password'],
        ]);

        clearstatcache();
        $this->assertTrue(\class_exists($className));

        $reflection = new ReflectionClass($className);

        $this->assertTrue($reflection->hasProperty('username'));
        $username = $reflection->getProperty('username');
        $this->assertEquals('string', $username->getType());
        $this->assertInstanceOf(Argument::class, $username->getAttributes()[0]->newInstance());
        $this->assertInstanceOf(Question::class, $username->getAttributes()[1]->newInstance());

        $this->assertTrue($reflection->hasProperty('password'));
        $password = $reflection->getProperty('password');
        $this->assertEquals('string', $password->getType());
        $this->assertInstanceOf(Argument::class, $password->getAttributes()[0]->newInstance());
        $this->assertInstanceOf(Question::class, $password->getAttributes()[1]->newInstance());
    }

    public function testAddOption(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Command\\OptionCommand';

        $this->console()->run('create:command', [
            'name' => 'Option',
            '--option' => ['isAdmin'],
        ]);

        clearstatcache();
        $this->assertTrue(\class_exists($className));

        $reflection = new ReflectionClass($className);

        $this->assertTrue($reflection->hasProperty('isAdmin'));
        $isAdmin = $reflection->getProperty('isAdmin');
        $this->assertEquals('bool', $isAdmin->getType());
        $this->assertInstanceOf(Option::class, $isAdmin->getAttributes()[0]->newInstance());
    }

    public function testScaffoldWithCustomNamespace(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Command\\SampleCommand';

        $this->console()->run('create:command', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Command',
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString(
            'App/Custom/Command/SampleCommand.php',
            \str_replace('\\', '/', $reflection->getFileName()),
        );
        $this->assertStringContainsString('App\Custom\Command', $content);
    }

    public function testShowInstructionAfterInstallation(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Command\\ArgumentCommand';

        $result = $this->console()->run('create:command', [
            'name' => 'Argument',
        ]);

        $output = $result->getOutput()->fetch();

        $this->assertSame(
            <<<OUTPUT
            Declaration of 'ArgumentCommand' has been successfully written into 'Command/ArgumentCommand.php'.

            Next steps:
            1. Use the following command to run your command: 'php app.php argument'
            2. Read more about user Commands in the documentation: https://spiral.dev/docs/console-commands

            OUTPUT,
            $output
        );
    }

    public static function commandDataProvider(): \Traversable
    {
        yield ['\\Spiral\\Tests\\Scaffolder\\App\\Command\\SampleCommand', 'sample', null, 'sample'];
        yield ['\\Spiral\\Tests\\Scaffolder\\App\\Command\\SomeCommand', 'SomeCommand', null, 'some:command'];
        yield [
            '\\Spiral\\Tests\\Scaffolder\\App\\Command\\SampleAliasCommand',
            'sampleAlias',
            'my-sample-command-alias',
            'my-sample-command-alias',
        ];
    }
}
