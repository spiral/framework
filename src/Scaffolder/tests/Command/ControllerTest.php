<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use ReflectionClass;
use ReflectionException;
use Spiral\Prototype\Traits\PrototypeTrait;
use Throwable;

class ControllerTest extends AbstractCommandTest
{
    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffold(): void
    {
        $class = '\\Spiral\\Tests\\Scaffolder\\App\\Controller\\SampleController';
        $this->console()->run('create:controller', [
            'name'      => 'sample',
            '--comment' => 'Sample Controller',
            '-a'        => ['index', 'save']
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString('strict_types=1', $content);
        $this->assertStringContainsString('{project-name}', $content);
        $this->assertStringContainsString('@author {author-name}', $content);
        $this->assertStringContainsString('Sample Controller', $reflection->getDocComment());
        $this->assertTrue($reflection->hasMethod('index'));
        $this->assertTrue($reflection->hasMethod('save'));

        $traits = $reflection->getTraitNames();

        $this->assertEmpty($traits);
        $this->assertNotContains(PrototypeTrait::class, $traits);

        $this->deleteDeclaration($class);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffoldWithCustomNamespace(): void
    {
        $class = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Controller\\SampleController';
        $this->console()->run('create:controller', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Controller'
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString(
            'App/Custom/Controller/SampleController.php',
            \str_replace('\\', '/', $reflection->getFileName())
        );
        $this->assertStringContainsString('App\Custom\Controller', $content);

        $this->deleteDeclaration($class);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testPrototypeTrait(): void
    {
        $class = '\\Spiral\\Tests\\Scaffolder\\App\\Controller\\Sample2Controller';
        $this->console()->run('create:controller', [
            'name'        => 'sample2',
            '--prototype' => true,
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($class));

        $reflection = new ReflectionClass($class);
        $traits = $reflection->getTraitNames();

        $this->assertNotEmpty($traits);
        $this->assertContains(PrototypeTrait::class, $traits);
        $this->deleteDeclaration($class);
    }
}
