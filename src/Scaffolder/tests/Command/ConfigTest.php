<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Input\StringInput;
use Throwable;

class ConfigTest extends AbstractCommandTestCase
{
    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffold(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Config\\SampleConfig';

        $this->console()->run('create:config', [
            'name' => 'sample',
            '--comment' => 'Sample Config'
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString('strict_types=1', $content);
        $this->assertStringContainsString('{project-name}', $content);
        $this->assertStringContainsString('@author {author-name}', $content);
        $this->assertStringContainsString('Sample Config', $reflection->getDocComment());

        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->hasConstant('CONFIG'));
        $this->assertTrue($reflection->hasProperty('config'));

        $this->assertIsString($reflection->getReflectionConstant('CONFIG')->getValue());
        $this->assertEquals([], $reflection->getDefaultProperties()['config']);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffoldWithCustomNamespace(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Config\\SampleConfig';

        $this->console()->run('create:config', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Config'
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString(
            'App/Custom/Config/SampleConfig.php',
            \str_replace('\\', '/', $reflection->getFileName())
        );
        $this->assertStringContainsString('App\Custom\Config', $content);
    }

    /**
     * @throws Throwable
     */
    public function testReverse(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Config\\ReversedConfig';
        $this->console()->run(null, new StringInput('create:config reversed -r'));

        clearstatcache();
        $this->assertTrue(class_exists($className));
    }

    /**
     * @throws Throwable
     */
    public function testReverseDefinition(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Config\\ReversedConfig';
        $this->console()->run('create:config', [
            'name' => 'reversed',
            '--comment' => 'Reversed Config',
            '--reverse' => true
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);

        $this->assertTrue($reflection->hasConstant('CONFIG'));
        $this->assertTrue($reflection->hasProperty('config'));

        $this->assertIsString($reflection->getReflectionConstant('CONFIG')->getValue());
        $this->assertIsArray($reflection->getDefaultProperties()['config']);
        $this->assertNotEmpty($reflection->getDefaultProperties()['config']);

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
            $this->assertArrayHasKey($method->name, $methods);

            if (!$method->hasReturnType()) {
                $this->assertNull($methods[$method->name]['hint']);
            } else {
                $this->assertEquals($methods[$method->name]['hint'], $method->getReturnType()->getName());
            }
        }

        $this->assertCount(count($methods), $reflectionMethods);
    }

    /**
     * @throws Throwable
     */
    public function testReverseWeirdKeys(): void
    {
        $this->className = $className = '\\Spiral\\Tests\\Scaffolder\\App\\Config\\WeirdConfig';
        $this->console()->run('create:config', [
            'name' => 'weird',
            '--comment' => 'Weird Config',
            '--reverse' => true
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);

        $this->assertTrue($reflection->hasConstant('CONFIG'));
        $this->assertTrue($reflection->hasProperty('config'));

        $this->assertIsString($reflection->getReflectionConstant('CONFIG')->getValue());
        $this->assertIsArray($reflection->getDefaultProperties()['config']);
        $this->assertNotEmpty($reflection->getDefaultProperties()['config']);

        $methods = [
            'getAthello',
            'getWithSpaces',
            'getAndOtherChars',
            'getWithUnderscoreAndDashes'
        ];

        $reflectionMethods = [];
        foreach ($reflection->getMethods() as $method) {
            if ($method->getDeclaringClass()->name !== $reflection->name) {
                continue;
            }
            $reflectionMethods[$method->name] = $method;

            $this->assertContains($method->name, $methods);
        }

        $this->assertCount(count($methods), $reflectionMethods);
    }

    /**
     * @throws Throwable
     */
    public function testConfigFile(): void
    {
        $filename = $this->createConfig('sample', 'Sample Config');
        $this->assertStringContainsString('strict_types=1', $this->files()->read($filename));
        $this->assertStringContainsString(
            '@see \\Spiral\\Tests\\Scaffolder\\App\\Config\\SampleConfig',
            $this->files()->read($filename)
        );

        $this->deleteConfigFile($filename);
    }

    /**
     * @throws Throwable
     */
    public function testConfigFileExists(): void
    {
        $this->className = '\\Spiral\\Tests\\Scaffolder\\App\\Config\\Sample2Config';

        $filename = $this->createConfig('sample2', 'Sample2 Config');
        $this->files()->append($filename, '//sample comment');

        $source = $this->files()->read($filename);
        $this->assertStringContainsString('//sample comment', $source);

        $filename = $this->createConfig('sample2', 'Sample2 Config');

        $source = $this->files()->read($filename);
        $this->assertStringContainsString('//sample comment', $source);

        $this->deleteConfigFile($filename);
    }

    public function testShowInstructionAfterInstallation(): void
    {
        $this->className = '\\Spiral\\Tests\\Scaffolder\\App\\Config\\InstructionConfig';

        $result = $this->console()->run('create:config', [
            'name' => 'instruction',
            '--comment' => 'Instruction Config'
        ]);

        $output = $result->getOutput()->fetch();

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<OUTPUT
            Declaration of 'InstructionConfig' has been successfully written into 'Config/InstructionConfig.php'.

            Next steps:
            1. You can now add your config values to the 'config/instruction.php' file.
            2. Read more about Config Objects in the documentation: https://spiral.dev/docs/framework-config

            OUTPUT,
            $output
        );
    }

    /**
     * @param string $filename
     * @throws Throwable
     */
    private function deleteConfigFile(string $filename): void
    {
        $this->files()->delete($filename);
    }

    /**
     * @param string $name
     * @param string $comment
     * @return string
     * @throws Throwable
     */
    private function createConfig(string $name, string $comment): string
    {
        $this->console()->run('create:config', [
            'name' => $name,
            '--comment' => $comment
        ]);

        clearstatcache();

        $filename = $this->app->directory('config') . "$name.php";
        $this->assertFileExists($filename);

        return $filename;
    }
}
