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
        self::assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);
        $content = $this->files()->read($reflection->getFileName());
        $classNameParts = \explode('\\', $className);

        $attributes = $reflection->getAttributes();

        /** @var AsCommand $definition */
        $definition = $attributes[0]->newInstance();

        self::assertStringContainsString('strict_types=1', $content);
        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->hasMethod('__invoke'));
        self::assertEquals($commandName, $definition->name);
        self::assertEquals('My sample command description', $definition->description);
        self::assertSame($classNameParts[\array_key_last($classNameParts)], $reflection->getShortName());
    }

    public function testAddArgument(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Command\\ArgumentCommand';

        $this->console()->run('create:command', [
            'name' => 'Argument',
            '--argument' => ['username', 'password'],
        ]);

        clearstatcache();
        self::assertTrue(\class_exists($className));

        $reflection = new ReflectionClass($className);

        self::assertTrue($reflection->hasProperty('username'));
        $username = $reflection->getProperty('username');
        self::assertEquals('string', $username->getType());
        self::assertInstanceOf(Argument::class, $username->getAttributes()[0]->newInstance());
        self::assertInstanceOf(Question::class, $username->getAttributes()[1]->newInstance());

        self::assertTrue($reflection->hasProperty('password'));
        $password = $reflection->getProperty('password');
        self::assertEquals('string', $password->getType());
        self::assertInstanceOf(Argument::class, $password->getAttributes()[0]->newInstance());
        self::assertInstanceOf(Question::class, $password->getAttributes()[1]->newInstance());
    }

    public function testAddOption(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Command\\OptionCommand';

        $this->console()->run('create:command', [
            'name' => 'Option',
            '--option' => ['isAdmin'],
        ]);

        clearstatcache();
        self::assertTrue(\class_exists($className));

        $reflection = new ReflectionClass($className);

        self::assertTrue($reflection->hasProperty('isAdmin'));
        $isAdmin = $reflection->getProperty('isAdmin');
        self::assertEquals('bool', $isAdmin->getType());
        self::assertInstanceOf(Option::class, $isAdmin->getAttributes()[0]->newInstance());
    }

    public function testScaffoldWithCustomNamespace(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Command\\SampleCommand';

        $this->console()->run('create:command', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Command',
        ]);

        clearstatcache();
        self::assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('App/Custom/Command/SampleCommand.php', \str_replace('\\', '/', $reflection->getFileName()));
        self::assertStringContainsString('App\Custom\Command', $content);
    }

    public function testShowInstructionAfterInstallation(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Command\\ArgumentCommand';

        $result = $this->console()->run('create:command', [
            'name' => 'Argument',
        ]);

        $output = $result->getOutput()->fetch();

        self::assertStringEqualsStringIgnoringLineEndings(<<<OUTPUT
            Declaration of 'ArgumentCommand' has been successfully written into 'Command/ArgumentCommand.php'.

            Next steps:
            1. Use the following command to run your command: 'php app.php argument'
            2. Read more about user Commands in the documentation: https://spiral.dev/docs/console-commands

            OUTPUT, $output);
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
