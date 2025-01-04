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
        self::assertTrue(\class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('strict_types=1', $content);
        self::assertStringContainsString('{project-name}', $content);
        self::assertStringContainsString('@author {author-name}', $content);
        self::assertStringContainsString('Sample Middleware', $reflection->getDocComment());
        self::assertTrue($reflection->hasMethod('process'));
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
        self::assertTrue(\class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('App/Custom/Middleware/SampleMiddleware.php', \str_replace('\\', '/', $reflection->getFileName()));
        self::assertStringContainsString('App\Custom\Middleware', $content);
    }

    public function testShowInstructionAfterInstallation(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Middleware\\SampleMiddleware';

        $result = $this->console()->run('create:middleware', [
            'name'      => 'sample-middleware',
        ]);

        $output = $result->getOutput()->fetch();

        self::assertStringEqualsStringIgnoringLineEndings(<<<OUTPUT
            Declaration of 'SampleMiddleware' has been successfully written into 'Middleware/SampleMiddleware.php'.

            Next steps:
            1. Don't forget to activate a middleware in the 'App\Application\Bootloader\RoutesBootloader'
            2. Read more about Middleware in the documentation: https://spiral.dev/docs/http-middleware

            OUTPUT, $output);
    }
}
