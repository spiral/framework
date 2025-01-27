<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use Spiral\Prototype\Traits\PrototypeTrait;

class ControllerTest extends AbstractCommandTestCase
{
    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testScaffold(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Controller\\SampleController';
        $this->console()->run('create:controller', [
            'name'      => 'sample',
            '--comment' => 'Sample Controller',
            '-a'        => ['index', 'save'],
        ]);

        \clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('strict_types=1', $content);
        self::assertStringContainsString('{project-name}', $content);
        self::assertStringContainsString('@author {author-name}', $content);
        self::assertStringContainsString('Sample Controller', $reflection->getDocComment());
        self::assertTrue($reflection->hasMethod('index'));
        self::assertTrue($reflection->hasMethod('save'));

        $traits = $reflection->getTraitNames();

        self::assertEmpty($traits);
        self::assertNotContains(PrototypeTrait::class, $traits);
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testScaffoldWithCustomNamespace(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Controller\\SampleController';
        $this->console()->run('create:controller', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Controller',
        ]);

        \clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('App/Custom/Controller/SampleController.php', \str_replace('\\', '/', $reflection->getFileName()));
        self::assertStringContainsString('App\Custom\Controller', $content);
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testPrototypeTrait(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Controller\\Sample2Controller';
        $this->console()->run('create:controller', [
            'name'        => 'sample2',
            '--prototype' => true,
        ]);

        \clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $traits = $reflection->getTraitNames();

        self::assertNotEmpty($traits);
        self::assertContains(PrototypeTrait::class, $traits);
    }

    public function testShowInstructionAfterInstallation(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Controller\\SampleController';
        $result = $this->console()->run('create:controller', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Controller',
        ]);

        $output = $result->getOutput()->fetch();

        self::assertStringEqualsStringIgnoringLineEndings(<<<OUTPUT
            Declaration of 'SampleController' has been successfully written into 'Custom/Controller/SampleController.php'.

            Next steps:
            1. Read more about Controllers in the documentation: https://spiral.dev/docs/http-routing

            OUTPUT, $output);
    }
}
