<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use ReflectionClass;
use ReflectionException;
use Throwable;

class MiddlewareTest extends AbstractCommandTestCase
{
    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffold(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Middleware\\SampleMiddleware';

        $this->console()->run('create:middleware', [
            'name'      => 'sample-middleware',
            '--comment' => 'Sample Middleware'
        ]);

        clearstatcache();
        $this->assertTrue(\class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString('strict_types=1', $content);
        $this->assertStringContainsString('{project-name}', $content);
        $this->assertStringContainsString('@author {author-name}', $content);
        $this->assertStringContainsString('Sample Middleware', $reflection->getDocComment());
        $this->assertTrue($reflection->hasMethod('process'));
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffoldWithCustomNamespace(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Middleware\\SampleMiddleware';

        $this->console()->run('create:middleware', [
            'name' => 'sample-middleware',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Middleware'
        ]);

        clearstatcache();
        $this->assertTrue(\class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString(
            'App/Custom/Middleware/SampleMiddleware.php',
            \str_replace('\\', '/', $reflection->getFileName())
        );
        $this->assertStringContainsString('App\Custom\Middleware', $content);
    }

    public function testShowInstructionAfterInstallation(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Middleware\\SampleMiddleware';

        $result = $this->console()->run('create:middleware', [
            'name'      => 'sample-middleware',
        ]);

        $output = $result->getOutput()->fetch();

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<OUTPUT
            Declaration of 'SampleMiddleware' has been successfully written into 'Middleware/SampleMiddleware.php'.

            Next steps:
            1. Don't forget to activate a middleware in the 'App\Application\Bootloader\RoutesBootloader'
            2. Read more about Middleware in the documentation: https://spiral.dev/docs/http-middleware

            OUTPUT,
            $output
        );
    }
}
