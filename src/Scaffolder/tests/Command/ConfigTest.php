<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use Symfony\Component\Console\Input\StringInput;

class ConfigTest extends AbstractCommandTestCase
{
    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testScaffold(): void
    {
        $this->className = $class = \Spiral\Tests\Scaffolder\App\Config\SampleConfig::class;

        $this->console()->run('create:config', [
            'name' => 'sample',
            '--comment' => 'Sample Config',
        ]);

        \clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('strict_types=1', $content);
        self::assertStringContainsString('{project-name}', $content);
        self::assertStringContainsString('@author {author-name}', $content);
        self::assertStringContainsString('Sample Config', $reflection->getDocComment());

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->hasConstant('CONFIG'));
        self::assertTrue($reflection->hasProperty('config'));

        self::assertIsString($reflection->getReflectionConstant('CONFIG')->getValue());
        self::assertEquals([], $reflection->getDefaultProperties()['config']);
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testScaffoldWithCustomNamespace(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Config\\SampleConfig';

        $this->console()->run('create:config', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Config',
        ]);

        \clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('App/Custom/Config/SampleConfig.php', \str_replace('\\', '/', $reflection->getFileName()));
        self::assertStringContainsString('App\Custom\Config', $content);
    }

    /**
     * @throws \Throwable
     */
    public function testReverse(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Config\\ReversedConfig';
        $this->console()->run(null, new StringInput('create:config reversed -r'));

        \clearstatcache();
        self::assertTrue(\class_exists($className));
    }

    /**
     * @throws \Throwable
     */
    public function testReverseDefinition(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Config\\ReversedConfig';
        $this->console()->run('create:config', [
            'name' => 'reversed',
            '--comment' => 'Reversed Config',
            '--reverse' => true,
        ]);

        \clearstatcache();
        self::assertTrue(\class_exists($className));

        $reflection = new \ReflectionClass($className);

        self::assertTrue($reflection->hasConstant('CONFIG'));
        self::assertTrue($reflection->hasProperty('config'));

        self::assertIsString($reflection->getReflectionConstant('CONFIG')->getValue());
        self::assertIsArray($reflection->getDefaultProperties()['config']);
        self::assertNotEmpty($reflection->getDefaultProperties()['config']);

        $methods = [
            'getStrParam' => ['hint' => 'string', 'annotation' => 'string'],
            'getIntParam' => ['hint' => 'int', 'annotation' => 'int'],
            'getFloatParam' => ['hint' => 'float', 'annotation' => 'float'],
            'getBoolParam' => ['hint' => 'bool', 'annotation' => 'bool'],
            'getNullParam' => ['hint' => null, 'annotation' => 'null'],

            'getArrParam' => ['hint' => 'array', 'annotation' => 'array|string[]'],

            'getMapParam' => ['hint' => 'array', 'annotation' => 'array|string[]'],
            'getMapParamBy' => ['hint' => 'string', 'annotation' => 'string'],

            'getMixedArrParam' => ['hint' => 'array', 'annotation' => 'array'],
            'getParams' => ['hint' => 'array', 'annotation' => 'array|string[]'],

            'getParameters' => ['hint' => 'array', 'annotation' => 'array|array[]'],
            'getParameter' => ['hint' => 'array', 'annotation' => 'array'],

            'getConflicts' => ['hint' => 'array', 'annotation' => 'array|array[]'],
            'getConflict' => ['hint' => 'string', 'annotation' => 'string'],
            'getConflictBy' => ['hint' => 'array', 'annotation' => 'array|int[]'],

            'getValues' => ['hint' => 'array', 'annotation' => 'array|array[]'],
            'getValue' => ['hint' => 'string', 'annotation' => 'string'],
            'getValueBy' => ['hint' => 'string', 'annotation' => 'string'],
        ];

        $reflectionMethods = [];
        foreach ($reflection->getMethods() as $method) {
            if ($method->getDeclaringClass()->name !== $reflection->name) {
                continue;
            }

            $reflectionMethods[$method->name] = $method;
            self::assertArrayHasKey($method->name, $methods);

            if (!$method->hasReturnType()) {
                self::assertNull($methods[$method->name]['hint']);
            } else {
                self::assertEquals($methods[$method->name]['hint'], $method->getReturnType()->getName());
            }
        }

        self::assertCount(\count($methods), $reflectionMethods);
    }

    /**
     * @throws \Throwable
     */
    public function testReverseWeirdKeys(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Config\\WeirdConfig';
        $this->console()->run('create:config', [
            'name' => 'weird',
            '--comment' => 'Weird Config',
            '--reverse' => true,
        ]);

        \clearstatcache();
        self::assertTrue(\class_exists($className));

        $reflection = new \ReflectionClass($className);

        self::assertTrue($reflection->hasConstant('CONFIG'));
        self::assertTrue($reflection->hasProperty('config'));

        self::assertIsString($reflection->getReflectionConstant('CONFIG')->getValue());
        self::assertIsArray($reflection->getDefaultProperties()['config']);
        self::assertNotEmpty($reflection->getDefaultProperties()['config']);

        $methods = [
            'getAthello',
            'getWithSpaces',
            'getAndOtherChars',
            'getWithUnderscoreAndDashes',
        ];

        $reflectionMethods = [];
        foreach ($reflection->getMethods() as $method) {
            if ($method->getDeclaringClass()->name !== $reflection->name) {
                continue;
            }
            $reflectionMethods[$method->name] = $method;

            self::assertContains($method->name, $methods);
        }

        self::assertCount(\count($methods), $reflectionMethods);
    }

    /**
     * @throws \Throwable
     */
    public function testConfigFile(): void
    {
        $filename = $this->createConfig('sample', 'Sample Config');
        self::assertStringContainsString('strict_types=1', $this->files()->read($filename));
        self::assertStringContainsString('@see \\Spiral\\Tests\\Scaffolder\\App\\Config\\SampleConfig', $this->files()->read($filename));

        $this->deleteConfigFile($filename);
    }

    /**
     * @throws \Throwable
     */
    public function testConfigFileExists(): void
    {
        $this->className = '\\Spiral\\Tests\\Scaffolder\\App\\Config\\Sample2Config';

        $filename = $this->createConfig('sample2', 'Sample2 Config');
        $this->files()->append($filename, '//sample comment');

        $source = $this->files()->read($filename);
        self::assertStringContainsString('//sample comment', $source);

        $filename = $this->createConfig('sample2', 'Sample2 Config');

        $source = $this->files()->read($filename);
        self::assertStringContainsString('//sample comment', $source);

        $this->deleteConfigFile($filename);
    }

    public function testShowInstructionAfterInstallation(): void
    {
        $this->className = '\\Spiral\\Tests\\Scaffolder\\App\\Config\\InstructionConfig';

        $result = $this->console()->run('create:config', [
            'name' => 'instruction',
            '--comment' => 'Instruction Config',
        ]);

        $output = $result->getOutput()->fetch();

        self::assertStringEqualsStringIgnoringLineEndings(<<<OUTPUT
            Declaration of 'InstructionConfig' has been successfully written into 'Config/InstructionConfig.php'.

            Next steps:
            1. You can now add your config values to the 'config/instruction.php' file.
            2. Read more about Config Objects in the documentation: https://spiral.dev/docs/framework-config

            OUTPUT, $output);
    }

    /**
     * @throws \Throwable
     */
    private function deleteConfigFile(string $filename): void
    {
        $this->files()->delete($filename);
    }

    /**
     * @throws \Throwable
     */
    private function createConfig(string $name, string $comment): string
    {
        $this->console()->run('create:config', [
            'name' => $name,
            '--comment' => $comment,
        ]);

        \clearstatcache();

        $filename = $this->app->directory('config') . "$name.php";
        self::assertFileExists($filename);

        return $filename;
    }
}
