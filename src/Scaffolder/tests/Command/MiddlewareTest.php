<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use ReflectionClass;
use ReflectionException;
use Throwable;

class MiddlewareTest extends AbstractCommandTest
{
    private const CLASS_NAME = '\\Spiral\\Tests\\Scaffolder\\App\\Middleware\\SampleMiddleware';

    public function tearDown(): void
    {
        $this->deleteDeclaration(self::CLASS_NAME);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffold(): void
    {
        $this->console()->run('create:middleware', [
            'name'      => 'sample-middleware',
            '--comment' => 'Sample Middleware'
        ]);

        clearstatcache();
        $this->assertTrue(class_exists(self::CLASS_NAME));

        $reflection = new ReflectionClass(self::CLASS_NAME);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString('strict_types=1', $content);
        $this->assertStringContainsString('{project-name}', $content);
        $this->assertStringContainsString('@author {author-name}', $content);
        $this->assertStringContainsString('Sample Middleware', $reflection->getDocComment());
        $this->assertTrue($reflection->hasMethod('process'));
    }
}
